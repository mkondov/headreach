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
use app\common\components\Helpers;

use yii\data\Pagination;

class SearchesController extends Controller {

	public $layout = 'app';
	public $session_data;
	
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
		}
	}
	
	public function actionIndex() {
		$wp_user_id = $this->session_data['current-user']['ID'];

		$jobs_model = new Job();

		$jobs_render = array();
		$omited = array();

		$page = ( isset($_GET['page']) ? $_GET['page'] : 1 );
		$sql_data = $jobs_model->getJobsData( $wp_user_id, $page );

		if ( $sql_data ) {
			$jobs = $sql_data['jobs'];

			foreach ($jobs as $i => $job) {
				$results = $jobs_model->getTaskInfluencers( $job['task_id'], 1, $wp_user_id );

				$people_found = ( isset($results['count']) ? $results['count'] : 0 );

				if ( $job['total_results'] AND $job['total_results'] > 50 ) {
					$people_found = $job['total_results'];
				}

				if ( $people_found ) {
					$job['count'] = $people_found;
					$jobs_render[] = $job;
				} else {
					$omited[] = $job;
				}

			}
		}

		return $this->render ( 'searches-index', [
						'jobs' => $jobs_render,
						'count' => $sql_data['count'],
						'count_with_results' => $sql_data['count_with_results'],
				] );
	}

}