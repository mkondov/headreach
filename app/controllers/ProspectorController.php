<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\Influencer;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\KeywordInputForm;
use app\models\Job;
use app\models\Task;
use app\models\IDMasking;
use app\models\CompanyModel;
use app\models\SocialSearches;
use app\models\HrEmails;
use app\models\JobQueries;
use app\common\components\Helpers;

use yii\data\Pagination;

class ProspectorController extends Controller {

	public $layout = 'app';
	public $session_data;
	public $blocked_ids = array(
		677, 688, 707, 708, 710, 711, 712, 754, 755, 757,
		610, 941, 948,
	);
	
	public function behaviors() {
		return [ 
				'access' => [ 
						'class' => AccessControl::className (),
						'only' => [ 
								'logout' 
						],
						'rules' => [ 
								[ 
										'actions' => [ 
												'logout' 
										],
										'allow' => true,
										'roles' => [ 
												'@' 
										] 
								] 
						] 
				],
				'verbs' => [ 
						'class' => VerbFilter::className (),
						'actions' => [ 
								'logout' => [ 
										'post' 
								] 
						] 
				] 
		];
	}
	
	public function actions() {
		return [ 
				'error' => [ 
						'class' => 'yii\web\ErrorAction' 
				],
				'captcha' => [ 
						'class' => 'yii\captcha\CaptchaAction',
						'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null 
				] 
		];
	}

	public function init() {
		$this->session_data = Helpers::getSessionData();

		if ( empty($this->session_data) ) {
			// $this->sendAdminMail( 'Init - Has Subscription parameter missing', json_encode( $this->session_data ) );
		}

		if ( !isset($this->session_data['current-user']) OR empty($this->session_data) ) {
			// $this->sendAdminMail( 'Before Redirect - Has Subscription parameter missing', json_encode( $this->session_data ) );
			$this->redirect( Yii::$app->params['websiteURL'] . '/login/?renew-session=1', 200 );
			return true;
		} else {
			$wp_user_id = $this->session_data['current-user']['ID'];

			if ( in_array($wp_user_id, $this->blocked_ids) ) {
				die( 'Unusual behavior detected!' );				
			}
			
		}
	}
	
	public function actionIndex() {
		return $this->render( 'prospector-index' );
	}

	public function actionNamesearch() {
		return $this->render( 'individual-search', [
						'type' => '1',
						'label' => 'Name',
				] );
	}

	public function actionCompanysearch() {
		return $this->render( 'individual-search', [
						'type' => '2',
						'label' => 'Company name',
				] );
	}

	public function actionWebsitesearch() {
		return $this->render( 'individual-search', [
						'type' => '3',
						'label' => 'Domain name',
				] );
	}

	public function actionPostscan() {
		return $this->render( 'individual-search', [
						'type' => '4',
						'label' => 'Post URL',
				] );
	}

	public function actionAdvancedsearch() {
		return $this->render( 'advanced-search', [
						'type' => '5',
						'label' => 'Advanced search',
				] );
	}

	public function actionPost() {

		$model = new KeywordInputForm();
		$queries_model = new JobQueries();
		$emails_model = new HrEmails();
		$wp_user_id = $this->session_data['current-user']['ID'];
		$has_sub = $this->checkForSubscription();

		// Do the Magic here
		$jobs = array();
		$influencers = array();
		$active_task = '';
		$main_job_data = '';
		$status = 'loading';

		if ( isset($_GET['id']) AND !empty($_GET['id']) ) {
			$masked_id = $_GET['id'];
			$id = IDMasking::unmaskID( $masked_id );
			$page = ( isset($_GET['page']) ? $_GET['page'] : 1 );

			$queries = $queries_model->getJobQueries( $id );

			$jobs_model = new Job();
			$jobs_model->job_id = $id;

			$main_job_data = $jobs_model->findOne( $id );

			$jobs = $jobs_model->getJobsTasks();
			$active_task = $jobs[0];

			$task_id = $active_task['id'];

			$results = $jobs_model->getTaskInfluencers( $task_id, $page, $wp_user_id, true );

			if ( $results ) {
				$tmp_influencers = $results['influencers'];
				$influencers = $this->extendDataWithSearchResults( $tmp_influencers, $wp_user_id );
				$influencers = $emails_model->extendDataWithEmails( $influencers );
			}

			$status = $this->checkReportStatus( $id );

			if ( $status == 'loading' ) {
				return $this->render ( 'results-loading', [
						'user_id' => $wp_user_id,
						'main_job_data' => $main_job_data,
						'masked_job_id' => $masked_id,
						'status' => $status,
						'has_sub' => $has_sub,
				] );
			}

		}
		
		return $this->render ( 'results', [
						'model' => $model,
						'user_id' => $wp_user_id,
						'tasks' => $jobs,
						'main_job_data' => $main_job_data,
						'influencers' => $influencers,
						'active_task' => $active_task,
						'count' => ( $results ? $results['count'] : '0' ),
						'status' => $status,
						'has_sub' => $has_sub,
						'queries' => $queries,
				] );
	}

	public function actionInfluencer() {

		if ( !isset($_POST['id']) OR empty($_POST['id']) ) {
			die( 'Invalid Request' );
		}

		$searchesModel = new SocialSearches();
		$taskController = new TaskController(array(), '');

		$wp_user_id = $this->session_data['current-user']['ID'];
		$id = IDMasking::unmaskID( $_POST['id'] );

		$emails_model = new HrEmails();

		$influencers = new Influencer();
		$influencer = $influencers->findOne( $id );

		$influencer = $emails_model->extendDataWithEmailsSingle( $influencer );

		$args = array(
			'influencer_id' => $id,
			'wp_user_id' => $wp_user_id,
		);
		
		$data = $searchesModel->findOne( $args );

		if ( $data ) {
			$results_found = ( $data->is_charged ? 1 : 0 );
			$is_searched = 1;
		} else {
			$results_found = 0;
			$is_searched = 0;
		}

		$company_data = '';

		if ( isset($influencer['company']) ) {
			$company_data = $taskController->getCompanyData( $influencer );
			$company_data = $company_data['full_data'];
		}

		if ( empty($influencer) ) {
			die( 'Invalid Request' );
		}

		usleep(500000);

		return $this->render ( 'influencer', [
						'influencer' => $influencer,
						'company_data' => $company_data,
						'results_found' => $results_found,
						'is_searched' => $is_searched,
				] );
	}

	public function actionExport() {

		if ( !isset($_GET['id']) OR empty($_GET['id']) ) {
			die( 'Invalid Request' );
		}

		$masked_id = $_GET['id'];
		$wp_user_id = $this->session_data['current-user']['ID'];
		$id = IDMasking::unmaskID( $masked_id );

		$emails_model = new HrEmails();

		$jobs_model = new Job();
		$jobs_model->job_id = $id;

		$main_job_data = $jobs_model->findOne( $id );

		$jobs = $jobs_model->getJobsTasks();

		$active_task = $jobs[0];

		$task_id = $active_task['id'];

		$results = $jobs_model->getInfluencersForExport( $task_id );
			
		if ( empty($results) ) {
			return false;
		}

		$influencers = $this->extendDataWithSearchResults( $results, $wp_user_id );
		$influencers = $emails_model->extendDataWithEmails( $influencers );

		$data_tbe = array();

		foreach ($influencers as $influencer) {

			$is_searched = $influencer['is_searched'];
			$has_smtp_validated_email = false;

			if ( $is_searched ) {
				if ( $influencer['person_social'] ) {
					$socials = json_decode($influencer['person_social']);
					$socials = implode(', ', $socials);
				}

				if ( $influencer['emails_data'] ) {
					$tmp_email_data = array();
					foreach ($influencer['emails_data'] as $tme) {
						$tmp_email_data[] = $tme['email'];

						if ( $tme['is_smtp_valid'] ) {
							$has_smtp_validated_email = true;
						}
					}

					$emails = implode(', ', $tmp_email_data);
				}
			} else {
				$socials = '';
				$emails = '';
			}

			$company_data = array();
			if ( !empty($influencer['company_json']) ) {
				$company_data = CompanyModel::getFormattedCompanyData( $influencer['company_json'] );
			}

			$names = Helpers::getName( $influencer['name'] );

			$tmp_data = array(
				'first_name' => $names['first_name'],
				'last_name' => $names['last_name'],
				'title' => $influencer['title'],
				'company' => $influencer['company'],
				'location' => $influencer['location'],
				'photo' => $influencer['photo_path'],
				'bio' => $influencer['bio'],
				'socials' => $socials,
				'emails' => $emails,
				'company_name' => $influencer['company_name'],
				'company_domain' => $influencer['company_domain'],
				'company_social_profiles' => ( isset($company_data['social_profiles']) ? $company_data['social_profiles'] : '' ),
				'company_email_addresses' => ( isset($company_data['email_addresses']) ? $company_data['email_addresses'] : '' ),
				'company_tel_numbers' => ( isset($company_data['tel_numbers']) ? $company_data['tel_numbers'] : '' ),
				'smtp_validated_email' => ( $has_smtp_validated_email ? 'yes' : '' ),
			);

			$data_tbe[] = $tmp_data;

			unset($tmp_data);
			unset($emails);
		}

		Helpers::downloadFile( 'data_export_'. $masked_id .'_' . date( 'Y-m-d' ) . '.csv');
		echo Helpers::array2csv( $data_tbe );
		die();
	}

	public function extendDataWithSearchResults( $influencers, $wp_user_id ) {
		$searchesModel = new SocialSearches();

		foreach ($influencers as $i => $influencer) {
			$args = array(
				'influencer_id' => ( isset($influencer['influencer_id']) ? $influencer['influencer_id'] : $influencer['id'] ),
				'wp_user_id' => $wp_user_id,
			);
			
			$data = $searchesModel->findOne( $args );

			if ( $data ) {
				$influencers[$i]['results_found'] = ( $data->is_charged ? 1 : 0 );
				$influencers[$i]['is_searched'] = 1;
			} else {
				$influencers[$i]['results_found'] = 0;
				$influencers[$i]['is_searched'] = 0;
			}

		}

		return $influencers;
	}

	public function reAppendSocials( $influencers ) {

		$influencer_model = new Influencer();

		foreach ($influencers as $i => $influencer) {
			$influencer = $influencer_model->findOne( $influencer['id'] );
			$influencers[$i]['person_social'] = $influencer->person_social;
		}
		
		return $influencers;
	}

	public function checkReportStatus( $report_id ) {
		$jobsModel = new Job();
		$job = $jobsModel->findOne( $report_id );
		$finished = ( $job->finished_at ? 'ready' : 'loading' );

		return $finished;
	}

	public function actionCheckstatus() {

		if ( !isset($_POST['id']) OR !intval($_POST['id']) ) {
			die( 'Missing Report ID' );
		}

		$report_id = IDMasking::unmaskID( $_POST['id'] );

		$finished = $this->checkReportStatus( $report_id );

		$result = array(
			'status' => $finished,
		);

		die( json_encode( $result ) );
	}

	public static function getSocials() {
		return array(
			'facebook' => 'www.facebook.com',
			'twitter' => 'twitter.com',
			'pinterest' => 'www.pinterest.com',
			'linkedin' => 'linkedin',
			'klout' => 'klout.com',
			'youtube' => 'youtube.com',
			'quora' => 'www.quora.com',
			'skype' => 'skype',
			'lastfm' => 'www.last.fm',
			'getsatisfaction' => 'getsatisfaction.com',
			'foursquare' => 'foursquare.com',
			'intensedebate' => 'intensedebate.com',
			'other' => 'instagram.com',
			'slideshare' => 'www.slideshare.net',
			'delicious' => 'delicious.com',
			'plancast' => 'www.plancast.com',
			'aboutme' => 'about.me',
			'myspace' => 'myspace.com',
			'angellist' => 'angel.co',
			'xing' => 'www.xing.com',
			'vimeo' => 'vimeo.com',
			'gravatar' => 'gravatar.com',
			'flickr' => 'www.flickr.com',
			'googleplus' => 'plus.google.com',
			'github' => 'github',
		);
	}

	public function actionGetcountries() {

		if ( !isset($_GET['query']) OR empty($_GET['query']) ) {
			return json_encode( array() );
		}

		$countries = Helpers::getCountries();

		$query = $_GET['query'];
		$response = $this->getACResponse( $query, $countries );

		return json_encode( $response );
	}

	public function actionGetlocations() {

		if ( !isset($_GET['query']) OR empty($_GET['query']) ) {
			return json_encode( array() );
		}

		$locations = Helpers::getLocations();

		$query = $_GET['query'];
		$response = $this->getACResponse( $query, $locations );

		return json_encode( $response );
	}

	public function actionGetindustries() {
		
		if ( !isset($_GET['query']) OR empty($_GET['query']) ) {
			return json_encode( array() );
		}

		$industries = Helpers::getIndustries();

		$query = $_GET['query'];
		$response = $this->getACResponse( $query, $industries );

		return json_encode( $response );
	}

	public function actionGetcompanies() {
		if ( !isset($_GET['query']) OR empty($_GET['query']) ) {
			return json_encode( array() );
		}

		$query = $_GET['query'];

		return $this->getClearbitResponse( $query, 'name' );
	}

	public function actionGetdomains() {
		if ( !isset($_GET['query']) OR empty($_GET['query']) ) {
			return json_encode( array() );
		}

		$query = $_GET['query'];

		return $this->getClearbitResponse( $query, 'domain' );
	}

	public function getClearbitResponse( $query, $return_param ) {
		$url = 'https://autocomplete.clearbit.com/v1/companies/suggest?query=' . urlencode( $query );
		$data = @file_get_contents($url);

		$response = array();

		$response['query'] = 'Unit';
		$response['suggestions'] = array();

		if ( empty($data) ) {
			return json_encode( $response );
		}

		$data = json_decode( $data, true );

		foreach ($data as $entry) {
			$response['suggestions'][] = array(
				'value' => ( $entry[$return_param] ? $entry[$return_param] : $query ),
				'data' => $entry['logo'],
			);
		}

		return json_encode( $response );
	}

	public function getACResponse( $query, $suggestions, $limit = 999 ) {
		$response = array();

		$response['query'] = 'Unit';
		$response['suggestions'] = array();

		$query = strtolower($query);
		$len = strlen($_GET['query']);
		$pointerlength = $len + 1;

		foreach ($suggestions as $suggestion) {

			$tmp_suggestion = strtolower($suggestion);
			$pointer = substr( $tmp_suggestion, 0, $pointerlength );

			if ( strstr($tmp_suggestion, $query) ) {
				if ( !isset($response[$pointer]) OR count($response[$pointer]) < $limit ) {
					$response['suggestions'][] = htmlspecialchars_decode( $suggestion );
				}
			}

		}

		return $response;
	}

	public function actionGetpositions() {

		if ( !isset($_GET['query']) OR empty($_GET['query']) ) {
			return json_encode( array() );
		}

		$file = ASSETS_PATH . 'positions.txt';
		$contents = file_get_contents( $file );
		$positions = explode(PHP_EOL, $contents);

		$query = $_GET['query'];
		$response = $this->getACResponse( $query, $positions );

		return json_encode( $response );
	}

	public function actionLoadmoreresults() {

		if ( !isset($_POST['queryID']) OR empty($_POST['queryID']) ) {
			die( 'Invalid Request' );
		}

		$id = IDMasking::unmaskID( $_POST['queryID'] );
		$wp_user_id = $this->session_data['current-user']['ID'];
		$has_sub = $this->checkForSubscription();

		$queries_model = new JobQueries();
		$curr_job = $queries_model->findOne( $id );

		if ( empty($curr_job) ) {
			die( 'Invalid Job' );
		}

		$influencer_model = new Influencer();
		$emails_model = new HrEmails();

		$influencers = $influencer_model->loadMoreResults( $id );

		if ( empty($influencers) ) {
			return false;
		}

		$influencers = $this->extendDataWithSearchResults( $influencers, $wp_user_id );
		$influencers = $emails_model->extendDataWithEmails( $influencers );
		$influencers = $this->reAppendSocials( $influencers );

		$job_id = $curr_job->job_id;

		$queries = $queries_model->getJobQueries( $job_id );

		$last_job = $queries_model->find()
					->where(['job_id' => $job_id])
					->orderBy(['id' => SORT_DESC])
					->one();

		return $this->renderPartial ( 'results-entries', [
				'influencers' => $influencers,
				'last_job' => $last_job,
				'total_queries' => count( $queries ),
				'has_sub' => $has_sub,
		] );
	}

	public function checkForSubscription() {

		if ( !isset($this->session_data['current-user']['has_sub']) ) {
			// $this->sendAdminMail( 'Has Subscription parameter missing', json_encode( $this->session_data ) );
			return true;
		}

		return true;

		$wp_user_id = $this->session_data['current-user']['ID'];
		$allowed_users = array(
			1,
		);

		if ( in_array($wp_user_id, $allowed_users) ) {
			return true;
		}

		// No subscription for this user
		if ( empty($this->session_data['current-user']['has_sub']) ) {
			return false;
		}

		return true;
	}

	public function sendAdminMail( $subject, $txt ) {
		$to = 'dogostz@gmail.com';
		$headers = "From: webmaster@headreach.com" . "\r\n" .
		"CC: support@headreach.com";
		mail($to, $subject, $txt, $headers);
	}

}