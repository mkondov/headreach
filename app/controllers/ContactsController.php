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
use app\models\HrEmails;
use app\models\CompanyModel;
use app\common\components\Helpers;
use yii\data\Pagination;

class ContactsController extends Controller {

	public $layout = 'app';
	public $session_data;
	public $blocked_ids = array(
		677, 688, 707, 708, 710, 711, 712, 754, 755, 757,
		610,
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
		$wp_user_id = $this->session_data['current-user']['ID'];

		$page = ( isset($_GET['page']) ? $_GET['page'] : 1 );

		$jobs_model = new Job();
		$emails_model = new HrEmails();

		$contacts = $jobs_model->getContacts( $page, $wp_user_id );

		$results = $contacts['contacts'];

		$results = $emails_model->extendDataWithEmails( $results );

		return $this->render ( 'contacts-index', [
						'contacts' => $results,
						'count' => $contacts['count'],
				] );
	}

	public function actionExport() {

		$type = 'emails-only';

		if ( isset($_GET['id']) AND $_GET['id'] == 'all' ) {
			$type = 'all';
		}

		$jobs_model = new Job();
		$emails_model = new HrEmails();

		$wp_user_id = $this->session_data['current-user']['ID'];

		$results = $jobs_model->getContactsWithCompany( 1, $wp_user_id, false );

		if ( empty($results) ) {
			return false;
		}

		$tmp_influencers = $results['contacts'];

		if ( empty($tmp_influencers) ) {
			return false;
		}

		$influencers = $emails_model->extendDataWithEmails( $tmp_influencers );

		$data_tbe = array();

		foreach ($influencers as $influencer) {

			if ( empty($influencer['emails_data']) AND $type == 'emails-only' ) {
				continue;
			}

			$company_data = array();
			if ( !empty($influencer['company_json']) ) {
				$company_data = CompanyModel::getFormattedCompanyData( $influencer['company_json'] );
			}

			if ( $influencer['person_social'] ) {
				$socials = json_decode($influencer['person_social']);
				$socials = implode(', ', $socials);
			}

			$tmp_email_data = array();
			$has_smtp_validated_email = false;
			foreach ($influencer['emails_data'] as $tme) {
				$tmp_email_data[] = $tme['email'];

				if ( $tme['is_smtp_valid'] ) {
					$has_smtp_validated_email = true;
				}
			}

			$emails = implode(', ', $tmp_email_data);

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
				'timestamp_searched' => date( 'Y-m-d H:i:s', strtotime($influencer['timestamp_searched']) ),
				'smtp_validated_email' => ( $has_smtp_validated_email ? 'yes' : '' ),
			);

			$data_tbe[] = $tmp_data;

			unset($tmp_data);
			unset($emails);
		}

		Helpers::downloadFile( 'data_export_' . date( 'Y-m-d' ) . '.csv');
		echo Helpers::array2csv( $data_tbe );
		die();
	}

}