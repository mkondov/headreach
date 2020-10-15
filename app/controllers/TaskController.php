<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\HrQuery;
use app\models\Job;
use app\models\Keyword;
use app\models\Sumo;
use app\models\SumoArticle;
use app\models\LinkedinScraper;
use app\models\Task;
use app\models\JobTaskMeta;
use app\models\Influencer;
use app\models\CommandsModel;
use app\models\SocialSearches;
use app\models\IDMasking;
use app\models\CompanyModel;
use app\models\CompanyMetaModel;
use app\models\FullContactApi;
use app\models\WPModel;
use app\models\GoogleAPI;
use app\models\GoogleQuery;
use app\models\HrEmails;
use app\common\components\Helpers;
use app\common\components\TLDFinder;


class TaskController extends Controller {
	
	// temp fix
	public $enableCsrfValidation = false;
	public $post_param;
	public $search_type;
	public $parameters;
	public $session_data;
	public $perform_validation = false;

	public $charge = false;

	public $blocked_ids = array(
		677, 688, 707, 708, 710, 711, 712, 754, 755, 757,
		610, 941, 948
	);

	public $layout = 'app';

	public $blacklisted_domains = array(
		'plus.google.com', 'twitter.com', 'facebook.com', 'www.facebook.com', 'www.linkedin.com',
		'www.youtube.com'
	);

	public $users_no_prev = array(
		281, 609
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
				] 
		];
	}

	public function init() {

		$this->session_data = Helpers::getSessionData();
		if ( empty($this->session_data) ) {
			$this->redirect( Yii::$app->params['websiteURL'] . '/login/?renew-session=1', 200 );
			return true;
		} else {
			$wp_user_id = $this->session_data['current-user']['ID'];

			if ( in_array($wp_user_id, $this->blocked_ids) ) {
				die( 'Unusual behavior detected!' );				
			}

			$users_with_validation = array(
				1, 1125, 1103, 1031, 20, 53
			);

			if ( in_array($wp_user_id, $users_with_validation) OR $wp_user_id > 1137 ) {
				$this->perform_validation = true;
			}

		}

	}
	
	public function actionExecutequery() {

		if (
			( !isset($_POST['parameter']) OR empty($_POST['parameter']) ) OR
			( !isset($_POST['search_type']) OR empty($_POST['search_type']) )
		) {
			die( 'Please input a parameter!' );
		}

		$this->post_param = $_POST['parameter'];
		$this->search_type = $_POST['search_type'];
		$this->parameters = ( isset($_POST['advanced_params']) ? $_POST['advanced_params'] : array() );
		$wp_user_id = $this->session_data['current-user']['ID'];
		// $has_sub = $this->session_data['current-user']['has_sub'];

		// $searches = Helpers::getSearches();
		// if ( !$has_sub AND $searches['left'] < 1 ) {
		// 	die( json_encode(array(
		// 		'inserted' => false,
		// 	)) );
		// }

		$cmd = 'php yii hello/run "' . $this->post_param . '" ' . $this->search_type . ' ' . $wp_user_id;

		if ( $this->parameters ) {
			setlocale(LC_CTYPE, "en_US.UTF-8");
			$cmd .= ' ' . escapeshellarg( serialize($this->parameters) );
		}

		$commandsModel = new CommandsModel();
		$commandsModel->command = $cmd;
		$commandsModel->executed = 0;
		$commandsModel->insert();

		// Get the last inserted Job for this user
		// There is room for error here, as the execution process is asynchronous

		sleep( 2 );

		$theJob = Job::find()
					->where(['wp_user_id' => $wp_user_id])
					->orderBy(['id' => SORT_DESC])
					->one();

		$latest_job_id = $theJob->id;
		$masked_id = IDMasking::maskID( $latest_job_id );

		$result = array(
			'inserted' => true,
			'query_url' => Yii::$app->params['webPath'] . '/prospector/post/' . $masked_id,
		);

		die( json_encode( $result ) );
	}

	public function actionRunapi() {

		$start = '';
		$key = 'AIzaSyCMGfdDaSfjqv5zYoS0mTJnOT3e9MURWkU';
		$cx = '006728377330183617582:4lqwegbhtm0';
		$query = 'dharmesh shah site:linkedin.com/in/ OR site:linkedin.com/pub/ -site:linkedin.com/pub/dir/ -site:ca.linkedin.com';

		for ($i=1; $i < 3; $i++) {
			$start = ( $i > 1 ? ($i-1) * 10 : 1 );
			$url = 'https://www.googleapis.com/customsearch/v1?key='. $key .'&cx='. $cx .'&q='. rawurlencode($query) . '&start=' . $start;
			echo $this->cURLGoogle( $url, $i );
		}

		exit( 'all done' );

	}

	public function cURLGoogle( $url, $page ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		// if ($p) {
		//     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		//     curl_setopt($ch, CURLOPT_POST, 1);
		//     curl_setopt($ch, CURLOPT_POSTFIELDS, $p);
		// }

		$result = curl_exec($ch);
		curl_close($ch);

		if ( $result ) {
			$results_arr = json_decode($result, true);

			ob_start();
			echo '<pre>';
			print_r( $results_arr['items'] );
			echo '</pre>';
			$results = ob_get_clean();

			return $results;
		}
	}

	public function actionExecutemanual() {

		$api_key = '9665565549022082356c025611754cb714ea2c7a';
		$url_search = 'https://api.anymailfinder.com/v3.0/search/person.json';

		// https://api.hunter.io/v2/email-finder?domain=lhsystems.com&amp;company=Lufthansa%20Systems&amp;first_name=Carsten&amp;last_name=Spohr&amp;api_key=229da7bf07fe3b3a313f575a48930d165cdae194

		$headers = array(
			'X-Api-Key: '. $api_key
		);

		$data['name'] = 'Daniel Minchev';
		$data['domain'] = 'https://appzio.com/';
		$data['include_pattern'] = true;

		$process = curl_init();
		curl_setopt($process, CURLOPT_URL,$url_search);
		curl_setopt($process, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		# curl_setopt($process, CURLOPT_USERPWD, "$login:$password");
		$result = curl_exec($process);
		curl_close($process);

		echo '<pre>';
		print_r( $result );
		echo '</pre>';
		exit;


		// APIs
		/*
		$fullContact = new FullContactApi();

		/// $extra_data = $fullContact->getPersonInfo( 'murry@metrilo.com' );

		// Find some essential company information
		$json_company_data = $fullContact->getCompanyInfo ( 'kurve.co.uk' );		
		$fc_company_info = json_decode ( $json_company_data );
		$fc_company_info = $fc_company_info [0];
		*/

	}

	public function actionFindsocials() {		
		
		if ( !isset($_POST['id']) ) {
			die( 'Cheating, huh?' );
		}

		$credits = Helpers::getCredits();
		if ( $credits['left'] == 0 ) {
			$html = $this->getNoCreditsHTML();
			return $html;
		}

		$domain = false;
		$email_found = false;
		$socials_found = false;

		$id = IDMasking::unmaskID( $_POST['id'] );

		$taskMetaModel = new JobTaskMeta();
		$fullContact = new FullContactApi();
		$emails_model = new HrEmails();

		$wp_user_id = $this->session_data['current-user']['ID'];

		$influencers = new Influencer();
		$influencers->wp_user_id = $wp_user_id;
		$influencer = $influencers->findOne( $id );

		if ( empty($influencer) ) {
			return false;
		}

		// Check for previous searches for this Influencer
		if ( $inf_data = $this->influencerAlreadySearched( $id ) ) {
			$this->charge = $inf_data['charge'];
			$email_found = $inf_data['email_found'];
			$socials_found = $inf_data['socials_found'];

			$influencer = $emails_model->extendDataWithEmailsSingle( $influencer );

			$this->doCharge();
			
			$this->handleCredits( $id, $email_found, $socials_found );
			$html = $this->getSocialsHTML( $influencer );

			return $html;
		}

		$name = $influencer->name;

		// Retrieve the complete company info
		$company_data = $this->getCompanyData( $influencer );

		$domain = $company_data['domain'];
		$company = $company_data['company'];

		$name_pieces = Helpers::getName( $name );
		$first_name = $name_pieces['first_name'];
		$last_name = $name_pieces['last_name'];

		$email_data = $influencers->getEmailViaAPI( $domain, $company, $first_name, $last_name, $use_all_sources = false );

		/*
		$disable_prev = false;
		if ( in_array($wp_user_id, $this->users_no_prev) ) {
			$disable_prev = true;
		}
		*/

		$disable_prev = true;

		// Email not found
		// Fallback for a search for previous companies
		if ( empty($email_data) AND !$disable_prev ) {
			
			$results = $this->handleOldCompanies( $influencer, $id );
			$this->charge = $results['charge'];
			$email_found = $results['email_found'];
			$socials_found = $results['socials_found'];

		} else if ( $email_data ) {
			$email_found = true;

			$email = $this->storeEmail( $email_data, $id );

			$extra_data = $fullContact->getPersonInfo( $email );

			if ( $extra_data ) {
				$fc_person_info = json_decode ( $extra_data );
				$fc_person_info = $fc_person_info [0];
				$response = json_decode( $fc_person_info->json_response );

				// Email found, but no results from Fullcontact
				if ( $response->status == '200' ) {
					if ( empty($influencer->company) ) {
						$influencer->company = $fc_person_info->company;
					}

					$influencer->bio = $fc_person_info->bio;
					
					if ( $fc_person_info->contact_info ) {
						$influencer->contact_info = json_encode( $fc_person_info->contact_info );
					}

					$influencer->person_social = $influencers->mapLinkedInSingle( $fc_person_info->social, $influencer );
					$influencer->full_contact_person_json = json_encode( $fc_person_info->json_response );

					$socials_found = true;
				}
			}

			$this->doCharge();
			$influencer->update();
		}

		$influencer = $emails_model->extendDataWithEmailsSingle( $influencer );

		$this->handleCredits( $id, $email_found, $socials_found );
		$html = $this->getSocialsHTML( $influencer );

		return $html;
	}

	public function actionRerunsocials() {		
		$credits = Helpers::getCredits();
		if ( $credits['left'] == 0 ) {
			$html = $this->getNoCreditsHTML();
			return $html;
		}

		if ( !isset($_POST['id']) ) {
			die( 'Cheating, huh?' );
		}

		$domain = false;
		$email_found = false;
		$socials_found = false;

		$id = IDMasking::unmaskID( $_POST['id'] );

		$taskMetaModel = new JobTaskMeta();
		$fullContact = new FullContactApi();
		$emails_model = new HrEmails();

		$influencers = new Influencer();
		$influencer = $influencers->findOne( $id );

		if ( empty($influencer) ) {
			return false;
		}

		$name = $influencer->name;

		// Retrieve the complete company info
		$company_data = $this->getCompanyData( $influencer );

		$domain = $company_data['domain'];
		$company = $company_data['company'];

		$name_pieces = Helpers::getName( $name );
		$first_name = $name_pieces['first_name'];
		$last_name = $name_pieces['last_name'];

		$email_data = $influencers->getEmailViaAPI( $domain, $company, $first_name, $last_name, $use_all_sources = false );

		// Email not found
		// Fallback for a search for previous companies
		if ( empty($email_data) ) {
			
			$results = $this->handleOldCompanies( $influencer, $id );
			$this->charge = $results['charge'];
			$email_found = $results['email_found'];
			$socials_found = $results['socials_found'];

		} else if ( !empty($email_data) ) {
			$email_found = true;

			$email = $this->storeEmail( $email_data, $id );

			$extra_data = $fullContact->getPersonInfo( $email );

			if ( $extra_data ) {
				$fc_person_info = json_decode ( $extra_data );
				$fc_person_info = $fc_person_info [0];
				$response = json_decode( $fc_person_info->json_response );

				// Email found, but no results from Fullcontact
				if ( $response->status == '200' ) {
					if ( empty($influencer->company) ) {
						$influencer->company = $fc_person_info->company;
					}

					$influencer->bio = $fc_person_info->bio;
					
					if ( $fc_person_info->contact_info ) {
						$influencer->contact_info = json_encode( $fc_person_info->contact_info );
					}

					$influencer->person_social = $influencers->mapLinkedInSingle( $fc_person_info->social, $influencer );
					$influencer->full_contact_person_json = json_encode( $fc_person_info->json_response );

					$socials_found = true;
				}
			}

			$this->doCharge();
			$influencer->update();
		}

		$influencer = $emails_model->extendDataWithEmailsSingle( $influencer );

		$this->handleCredits( $id, $email_found, $socials_found );
		$html = $this->getSocialsHTML( $influencer );

		return $html;
	}

	public function storeEmail( $email_data, $influencer_id ) {
		$source = $email_data['source'];

		switch ($source) {
			case 'emailhunter':
				$email = $email_data['data']['email'];
				$score = $email_data['data']['score'];
				break;

			case 'voilanorbert':
				$email = $email_data['email']['email'];
				$score = $email_data['email']['score'];
				break;

			case 'anymailfinder':
				$email = $email_data['best_guess'];
				$class = $email_data['email_class'];

				if ( $class == 'validated' ) {
					$score = '95';
				} else if ( $class == 'pattern' ) {
					$score = '65';
				} else if ( $class == 'crawled' ) {
					$score = '85';
				}

				break;
		}

		$emails_model = new HrEmails();

		$args = array (
			'email' => $email,
		);

		$result = $emails_model->findOne( $args );
		
		if ( $result ) {
			return $email;
		}

		$validation_data = '';

		// Validate the email here
		if ( $this->perform_validation ) {
			$validation_data = $this->performValidation( $email );
		}

		if ( isset($validation_data['is_smtp_valid']) AND !empty($validation_data['is_smtp_valid']) ) {
			$this->charge = true;
		}

		$emails_model->influencer_id = $influencer_id;
		$emails_model->email = $email;
		$emails_model->score = $score;
		$emails_model->source = $source;
		$emails_model->api_response = json_encode( $email_data );
		$emails_model->validation_data = ( isset($validation_data['json_response']) ? $validation_data['json_response'] : '' );
		$emails_model->is_smtp_valid = ( isset($validation_data['is_smtp_valid']) ? $validation_data['is_smtp_valid'] : '' );
		$emails_model->insert();

		return $email;
	}

	public function performValidation( $email ) {

		if ( empty($email) ) {
			return false;
		}

		$api_key = 'd93c26eeb5100afbad9b76479412c748';
		$url = 'https://apilayer.net/api/check?access_key='. $api_key . '&catch_all=1&email=' . urlencode($email);

		$contents = @file_get_contents( $url );

		if ( empty($contents) ) {
			return false;
		}

		$data = json_decode( $contents, true );

		return array(
			'json_response' => $contents,
			'is_smtp_valid' => $data['smtp_check'],
		);
	}

	/*
	* Handles the actual credits charging point
	* This method is only related with the WordPress based credits management
	*/
	public function doCharge() {

		if ( !$this->charge ) {
			return false;
		}

		$WPModel = new WPModel();

		$wp_user_id = $this->session_data['current-user']['ID'];
		$credits_wallet = $WPModel->findOne( array( 'wp_user_id' => $wp_user_id ) );
		$current_credits = $credits_wallet->credits;
		
		// Decrement the current credits by one
		$credits_wallet->credits = $current_credits - 1;
		$credits_wallet->update();

		$websiteURL = Yii::$app->params['websiteURL'];

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $websiteURL . '/?check-credits=' . $wp_user_id
		));

		$result = curl_exec($curl);
	}

	public function influencerAlreadySearched( $id ) {
		$searchesModel = new SocialSearches();
		$influencer = $searchesModel->findOne(array(
			'influencer_id' => $id
		));

		if ( empty($influencer) ) {
			return false;
		}

		return array(
			'charge' => $influencer->is_charged,
			'email_found' => $influencer->email_found,
			'socials_found' => $influencer->socials_found,
		);
	}

	/*
	* This method is intended to associate a person with a Company entry
	* The idea is that we would be searching for the real company data only after somebody
	* tries to access the full view of a profile
	*/
	public function getCompanyData( $influencer ) {
		
		$influencers = new Influencer();
		$fullContact = new FullContactApi();
		$companyModel = new CompanyModel();
		$companyMetaModel = new CompanyMetaModel();

		$company = $influencer['company'];
		$company_data = '';
		$company_arr = array();

		// First - search for an association for this person
		$assoc_data = $companyMetaModel->getUserCompany( $influencer['id'] );

		if ( $assoc_data ) {
			return $assoc_data;
		}

		$domain = $this->getGDomain( $company );

		// Clearbit search
		if ( empty($domain) ) {
			$domain = $influencers->getDomainByCompanyName( $company );
		}

		// Search and store the Company data based on the Fullcontact API
		if ( $domain ) {
			$db_company_entry = $companyModel->getDBCompany( $domain );

			if ( $db_company_entry ) {
				$company_data = $db_company_entry;
			} else {
				// Find some essential company information
				$json_company_data = $fullContact->getCompanyInfo ( $domain );
				$company_data = json_decode( $json_company_data );
			}

			if ( empty($company_data->name) ) {
				$company_data->name = $domain;
			}

			$company_name = $company_data->name;

			$company_arr = $companyModel->insertCompany( $company_data );
			$company_id = $company_arr['id'];
			$companyMetaModel->addAssociation( $company_id, $influencer['id'] );
		}

		return array(
			'full_data' => $company_data,
			'company' => ( isset($company_arr['company']) ? $company_arr['company'] : '' ),
			'domain' => ( isset($company_arr['domain']) ? $company_arr['domain'] : '' ),
		);
	}

	public function getGDomain( $company ) {
		$gAPI = new GoogleAPI();
		$influencers = new Influencer();

		$api_results = $gAPI->getCompanyResults( '"'. $company .'" intext:website+http site:linkedin.com/company/' );

		if ( empty($api_results) ) {
			return false;
		}

		$result = $api_results['items'][0];

		$snippet = $result['snippet'];

		// $pattern = "@(http\:\/\/|https\:\/\/)?([a-z0-9][a-z0-9\-]*\.)+[a-z0-9][a-z0-9\-]*@";
		$pattern = "~((https?://)?([-\\w]+\\.[-\\w\\.]+)+\\w(:\\d+)?(/([-\\w/_\\.]*(\\?\\S+)?)?)*)~";
		// $pattern = "~(http|ftp)(s)?://([\w-]+\.)+[\w-]+(/[\w- ./?%&=]*)?~";

		// Sometimes there is an extra space in the returned website
		$snippet = str_replace(PHP_EOL, '', $snippet);

		preg_match( $pattern, $snippet, $matches );

		if ( empty($matches) ) {
			return false;
		}

		// We always get the first result
		$match = $matches[0];

		$match = urlencode( $match );

		$match = str_replace('%EF%BF%BD', '', $match);
		$match = str_replace('%C2', '', $match);
		$match = urldecode( $match );
		
		$domain = strtolower($match);

		if ( $domain ) {
			$tld_domain = TLDFinder::getTld( $domain, false, true );

			if ( $tld_domain ) {
				return $tld_domain;
			} else {
				return $domain;
			}
		}

		return $domain;
	}

	public function handleCredits( $influencer_id, $email_found, $socials_found ) {

		$wp_user_id = $this->session_data['current-user']['ID'];

		$searchesModel = new SocialSearches();

		// Look for an existing entry first
		$entry = $searchesModel->findOne(array(
			'influencer_id' => $influencer_id,
			'wp_user_id' => $wp_user_id,
		));

		if ( $entry ) {
			$entry->is_charged = $this->charge;
			$entry->email_found = $email_found;
			$entry->socials_found = $socials_found;
			$entry->update();

			return true;
		}

		$searchesModel->wp_user_id = $this->session_data['current-user']['ID'];
		$searchesModel->influencer_id = $influencer_id;
		$searchesModel->is_charged = $this->charge;
		$searchesModel->email_found = $email_found;
		$searchesModel->socials_found = $socials_found;
		$searchesModel->insert();

		return true;
	}

	public function getSocialsHTML( $influencer ) {
		return $this->renderPartial ( 'influencer-socials', [
						'influencer' => $influencer,
						'charge' => $this->charge,
						'wp_user_id' => $this->session_data['current-user']['ID'],
				] );
	}

	public function getNoCreditsHTML() {
		return $this->renderPartial ( 'influencer-no-credits' );	
	}

	public function getQuery( $parameters ) {

		$param_data = unserialize($parameters);

		// If the unserialization fails
		if ( empty($param_data) ) {
			return false;
		}

		$query = '';

		foreach ($param_data as $param => $data) {
			$ct = count($data);
			
			switch ($param) {
				case 'company':

					if ( isset($param_data['current_only']) AND !empty($param_data['current_only']) ) {
						$query .= 'more:pagemap:person-org:';
						
						foreach ($data as $i => $d) {
							$query .= $this->getQueryString( $d );
							if ( $ct > ($i+1) )
								$query .= ',';
						}
						
						$query .= ' ';
					} else {

						$query .= '(';

						foreach ($data as $i => $d) {
							$query .= '"' . $d . '"';
							if ( $ct > ($i+1) )
								$query .= ' OR ';
						}

						$query .= ')';

					}

					// Exclude from -intitle
					foreach ($data as $exc_d) {
						$query .= ' -intitle:"'. $d .'" ';
					}

					break;

				case 'name':
					$query .= ' AND (';
					foreach ($data as $i => $d) {
						$query .= 'intitle:"'. $d .'"';
						if ( $ct > ($i+1) )
							$query .= ' OR ';
					}
					$query .= ') ';

					break;
				
				case 'position':
					$query .= ' AND ';
					$query .= 'more:pagemap:person-role:';
					
					foreach ($data as $i => $d) {
						$query .= $this->getQueryString( $d );
						if ( $ct > ($i+1) )
							$query .= ',';
					}

					$query .= ' ';
					break;

				case 'industry':
				case 'keywords':
				case 'degree':
					$query .= ' AND (';
					
					foreach ($data as $i => $d) {

						if ( $param == 'start_date' OR $param == 'degree' ) {
							$query .= '"' . $d . '"';
						} else {
							$query .= '"' . str_replace(' ', '*', $d) . '"';
						}

						if ( $ct > ($i+1) )
							$query .= ' OR ';
					}

					$query .= ') ';
					break;

				case 'school':
					$query .= ' AND (';
					
					foreach ($data as $i => $d) {
						$query .= '"' . $d . '"';
						if ( $ct > ($i+1) )
							$query .= ' OR ';
					}

					$query .= ') ';
					break;

				case 'start_date':
					$query .= ' AND (';
					
					foreach ($data as $i => $d) {
						$query .= '"' . $d . ' * present' . '"';
						if ( $ct > ($i+1) )
							$query .= ' OR ';
					}

					$query .= ') ';
					break;

				case 'location':
					$query .= ' AND ';
					$query .= 'more:pagemap:person-location:';
					
					foreach ($data as $i => $d) {
						$query .= $this->getQueryString( $d );
						if ( $ct > ($i+1) )
							$query .= ',';
					}

					$query .= ' ';
					break;

			}

			unset($ct);
			unset($d);
		}

		if ( isset($param_data['country']) AND !empty($param_data['country']) ) {
			$countries = $param_data['country'];
			$total_c = count($countries);
			$list = Helpers::countriesMap();

			if ( $total_c > 1 ) {
				$query .= '(';
			}

			foreach ($countries as $j => $country) {
				$subdomain = array_search($country, $list);

				$query .= 'site:'. $subdomain .'/in/ OR site:'. $subdomain .'/pub/';

				if ( $total_c > ($j+1) )
					$query .= ' OR ';
			}

			if ( $total_c > 1 ) {
				$query .= ')';
			}
			
		} else {
			$query .= ' site:linkedin.com/in/ OR site:linkedin.com/pub/';
		}

		$query .= ' -site:linkedin.com/pub/dir/';

		$query = str_replace('  ', ' ', $query);
		$query = preg_replace('~^ AND ~', '', $query);

		return $query;
	}

	public function getQueryString( $string ) {
		$trim_chars = array( '.', '|', ',', '+', '-', '!', '—', '–', '&', '^', '!', '@', '$', '(', ')', '<', '>', ';', ';', '~', '`', ' ' );
		return str_replace($trim_chars, '*', $string);
	}

	public function handleOldCompanies( $influencer, $influencer_id ) {
		$socials = @json_decode( $influencer->person_social, true );

		if ( empty($socials) ) {
			return false;
		}

		$email_found = false;
		$socials_found = false;

		$name = $influencer->name;

		$linkedin = '';
		foreach ($socials as $social) {
			if ( preg_match('~linkedin~', $social) ) {
				$linkedin = $social;
				break;
			}
		}

		if ( empty($linkedin) ) {
			return array(
				'charge' => false,
				'email_found' => false,
				'socials_found' => false,
			);
		}

		$gAPI = new GoogleAPI();
		$influencers = new Influencer();
		$fullContact = new FullContactApi();

		$influencers->wp_user_id = $this->session_data['current-user']['ID'];

		$api_results = $gAPI->getCompanyResults( 'intext:previous OR intext:current inurl:' . $linkedin );

		if ( empty($api_results) ) {
			return array(
				'charge' => false,
				'email_found' => false,
				'socials_found' => false,
			);
		}

		$entry = $api_results['items'][0];
		$snippet = $entry['snippet'];
		$split = explode('Previous.', $snippet);

		// No previous companies
		if ( !isset($split[1]) ) {
			return array(
				'charge' => false,
				'email_found' => false,
				'socials_found' => false,
			);
		}

		$name_pieces = Helpers::getName( $name );
		$first_name = $name_pieces['first_name'];
		$last_name = $name_pieces['last_name'];

		$prev = $split[1];
		$prev_split = explode('Education.', $prev);
		$prev = $prev_split[0];
		$prev_split = explode(',;', $prev);

		$all_found_mails = array();

		foreach ($prev_split as $i => $result) {
			if ( strpos($result, '...') ) {
				$result = explode('...', $result);
				$result = $result[0];
			}

			$domain = $this->getGDomain( $result );

			if ( empty($domain) ) {
				continue;
			}

			$email_data = $influencers->getEmailViaAPI( $domain, false, $first_name, $last_name, $use_all_sources = false );

			if ( $email_data ) {
				$all_found_mails[] = $this->storeEmail( $email_data, $influencer_id );
			}
		}

		// Use the first email to search for social profiles
		if ( isset($all_found_mails[0]) ) {
			$email = $all_found_mails[0];

			$email_found = true;

			$extra_data = $fullContact->getPersonInfo( $email );

			if ( $extra_data ) {
				$fc_person_info = json_decode ( $extra_data );
				$fc_person_info = $fc_person_info [0];
				$response = json_decode( $fc_person_info->json_response );

				// Email found, but no results from Fullcontact
				if ( $response->status == '200' ) {
					if ( empty($influencer->company) ) {
						$influencer->company = $fc_person_info->company;
					}

					$influencer->bio = $fc_person_info->bio;
					
					if ( $fc_person_info->contact_info ) {
						$influencer->contact_info = json_encode( $fc_person_info->contact_info );
					}

					$influencer->person_social = $influencers->mapLinkedInSingle( $fc_person_info->social, $influencer );
					$influencer->full_contact_person_json = json_encode( $fc_person_info->json_response );

					$socials_found = true;
				}
			}

			$this->doCharge();
			$influencer->update();
		}

		return array(
			'charge' => $this->charge,
			'email_found' => $email_found,
			'socials_found' => $socials_found,
		);
	}

}