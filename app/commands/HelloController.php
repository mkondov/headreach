<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;

use app\models\HrQuery;
use app\models\Job;
use app\models\Keyword;
use app\models\Sumo;
use app\models\SumoArticle;
use app\models\LinkedinScraper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Task;
use app\models\IDMasking;
use app\models\Influencer;
use app\models\FullContactApi;
use app\models\CompanyModel;
use app\models\GoogleQuery;
use app\common\components\TLDFinder;


/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{

	public $job;

	public $post_param;
	public $parameters;
	public $search_type;

	public $blacklisted_domains = array(
		'plus.google.com', 'twitter.com', 'facebook.com', 'www.facebook.com', 'www.linkedin.com',
		'www.youtube.com'
	);

    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world') {


    	$connection = Yii::$app->db;

		$sql = "SELECT * FROM hr_google_queries
				WHERE md5_string = '' OR md5_string IS null
				ORDER BY id ASC
				LIMIT 1000";
		
		$command = $connection->createCommand ( $sql );
		
		$queries = $command->queryAll();

		$model = new GoogleQuery();

		foreach ($queries as $query) {
			$url = $query['url'];

			$entry = $model->findOne( $query['id'] );

			$entry->md5_string = md5($url);
			$entry->update();

			echo ' ' . $query['id'] . ' updated';
		}
		
		die( 'all done' );
    }

    public function actionRun( $post_param, $search_type, $wp_user_id, $adv_parameters = false ) {

    	define("ASSETS_PATH", __DIR__.'/../assets/');

    	$job_model = new Job();

    	$this->post_param = $post_param;
		$this->search_type = $search_type;
		$this->parameters = $adv_parameters;

		$job_model->wp_user_id = $wp_user_id;
		$job_model->search_type = $search_type;
		$job_model->search_term = $post_param;
		$job_model->started_at = time();
		$job_model->parameters = $adv_parameters;
		$job_model->insert();
		// $job_model->save( false );

		$job_id = $job_model->id; // Get the last inserted Job ID
		$this->job = $job_model->findOne( $job_id );

		switch ($search_type) {
			case '1':
				$this->findPeopleByName();
				break;

			case '2':
				$this->findPeopleByCompany();
				break;

			case '3':
				$this->findPeopleByDomain();
				break;
			
			case '4':
				$this->findMentions();
				break;

			case '5':
				$this->findPeopleAdvanced();
				break;
		}

		$this->job->finished_at = time ();
		$this->job->save ( false );        
    }

    public function findPeopleByName() {

		$task_id = $this->job->startTask( 'Find Person' );

		$person_module = new Influencer();
		$person_module->job_id = $this->job->id;
		$person_module->job_task_id = $task_id;

		$person_module->savePeople( $this->post_param, $pages = 1 );

		$this->job->finishTask();
	}

	public function findPeopleByCompany() {
		define(ASSETS_PATH, '/home/dogostz/webapps/headreach/app/assets/');

		$task_id = $this->job->startTask( 'Find Employees By Company' );

		$person_module = new Influencer();
		
		$person_module->job_id = $this->job->id;
		$person_module->job_task_id = $task_id;
		
		$person_module->savePeopleByCompany( $this->post_param, false, 'company', $pages = 1 );

		$this->job->finishTask();
	}

	public function findPeopleByDomain() {
		define(ASSETS_PATH, '/home/dogostz/webapps/headreach/app/assets/');
		
		$task_id = $this->job->startTask( 'Find Employees By Domain name' );

		$fullContact = new FullContactApi();
		
		$person_module = new Influencer();
		$person_module->job_id = $this->job->id;
		$person_module->job_task_id = $task_id;
		
		$tld_domain = TLDFinder::getTld( $this->post_param, false, true );

		if ( $tld_domain ) {
			$domain = $tld_domain;
		} else {
			$domain = $this->post_param;
		}

		// Find some essential company information
		$json_company_data = $fullContact->getCompanyInfo ( $domain );		
		$fc_company_info = json_decode ( $json_company_data );

		if ( !empty($fc_company_info->name) ) {
			$company_name = $fc_company_info->name;

			// Save the Company's data
			$company_model = new CompanyModel();
			$company_model->insertCompany( $fc_company_info );
		} else if ( $tmp_company_name = $person_module->getCompanyNameByDomain( $domain ) ) {
			$company_name = $person_module->getCompanyNameByDomain( $domain );
		} else {
			$company_name = $domain;
		}
		
		$person_module->savePeopleByCompany( $company_name, false, 'company', $pages = 1 );

		$this->job->finishTask();
	}

    public function findMentions() {
		
		$task_id = $this->job->startTask( 'Mentions' );
		
		$externalUrls = $this->extractExternalUrls();
		$names = $this->extractNames();
	
		$person_module = new Influencer();
		$person_module->job_id = $this->job->id;
		$person_module->job_task_id = $task_id;

		$after = array();

		if ( $names ) {
			foreach ($names as $name) {
				$name_is_valid = $person_module->checkName( $name );
				if ( $name_is_valid ) {
					// $after[] = $name;
					$person_module->savePeople( $name );
				}
			}
		}
		
		if ( $externalUrls ) {
			foreach ($externalUrls as $url) {
				$person_module->saveMentionsData( $url );

				// Find out if there is an influencer in this context
				// $influencers = $person_module->findExistingInfluencers( $url );

				// if ( empty($influencers) ) {
				// } else {
				// 	$person_module->associateInfluencers( $influencers, $url, null );
				// }
			}
		}
		
		$this->job->finishTask();
	}

	public function findPeopleAdvanced() {
		define(ASSETS_PATH, '/home/dogostz/webapps/headreach/app/assets/');

		$task_id = $this->job->startTask( 'Advanced people finder' );

		$person_module = new Influencer();
		$person_module->job_id = $this->job->id;
		$person_module->job_task_id = $task_id;
		
		$person_module->advancedSearch( $this->parameters );

		$this->job->finishTask();
	}

	public function actionUpdate( $id ) {
		$jobs_model = new Job();
		$jobs_model->job_id = $id;

		$main_job_data = $jobs_model->findOne( $id );

		$jobs = $jobs_model->getJobsTasks();
		$active_task = $jobs[0];
		$task_id = $active_task['id'];

		$results = $jobs_model->getTaskInfluencers( $task_id, $page, false );
		$influencers = $results['influencers'];

		$person_module = new Influencer();
		$person_module->updateRecords( $influencers );
	}

	private function extractExternalUrls() {

		$ch = curl_init ();
		$timeout = 5;
		curl_setopt ( $ch, CURLOPT_URL, $this->post_param );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt ( $ch, CURLOPT_ENCODING, 'UTF-8' );
		curl_setopt ( $ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		$html_data = curl_exec ( $ch );
		curl_close ( $ch );

		$dom = new \DomDocument('1.0', 'utf-8');
		libxml_use_internal_errors(true);
		@$dom->loadHTML(mb_convert_encoding($html_data, 'HTML-ENTITIES', 'UTF-8'));

		// $dom = new \DomDocument();
		// $dom->loadHTML($html_data, 'HTML-ENTITIES', 'UTF-8');
		$domains = array();

		foreach ($dom->getElementsByTagName('a') as $a) {
		    $domains[] = $a->getAttribute( 'href' );
		}

		/*
		$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		if (preg_match_all ( "/$regexp/siU", $html_data, $matches )) {
			$domains = $matches [2];
		}
		*/
		
		$uq_domains = array ();

		foreach ($domains as $domain) {
			if ( isset( parse_url($domain)['host'] ) ) {
				$uq_domains[] = parse_url( $domain )['host'];
			}
		}

		$tmp_domains_holder = array();

		$uq_domains = array_unique ( $uq_domains );
		$uq_domains = array_values ( $uq_domains );

		$refined = array();

		foreach ($uq_domains as $dm) {
			if ( in_array($dm, $this->blacklisted_domains) ) {
				continue;
			}

			$refined[] = $dm;
		}

		// foreach ($refined as $d) {
		// 	if ( preg_match("/[^\.\/]+\.[^\.\/]+$/", $d, $matches) ) {
		// 		$tmp_domains_holder[] = $matches[0];
		// 	}
		// }
		
		return $refined;
	}

	private function extractNames() {
		$scraper = new LinkedinScraper();

		$results = $scraper->extractNames( $this->post_param );

		if ( empty($results) ) {
			return false;
		}

		$results = json_decode( $results, true );

		if ( !isset($results['results']) OR empty($results['results']) ) {
			return false;
		}

		return $results['results'];
	}

}