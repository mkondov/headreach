<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\KeywordInputForm;
use app\models\Job;
use app\models\Task;
use app\models\IDMasking;

class SiteController extends Controller {

	public $layout = 'app';
	
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
	
	public function actionIndex() {
		return $this->render ( 'index' );
	}
	
	public function actionSubmitkeyword() {
		// Yii::trace('submit keyword page accessed', __METHOD__);
		
		$model = new KeywordInputForm();

		// Do the Magic here
		$jobs = array();
		$influencers = array();
		$active_task = '';
		$main_job_data = '';

		if ( isset($_GET['id']) AND !empty($_GET['id']) ) {
			$id = IDMasking::unmaskID( $_GET['id'] );

			$jobs_model = new Job();
			$jobs_model->job_id = $id;

			$main_job_data = $jobs_model->findOne( $id );

			$jobs = $jobs_model->getJobsTasks();

			// Convert jobs
			$jobs_handler = array();
			foreach ($jobs as $job) {
				$key = strtolower($job['step']);
				$jobs_handler[$key] = $job;
			}

			$active_task = $jobs_handler['mentions'];

			$context = 'url';

			if ( isset($_GET['task']) AND !empty($_GET['task']) ) {
				$active_task = $jobs_handler[$_GET['task']];
				if ( $_GET['task'] == 'influencers' ) {
					$context = 'keyword';
				}
			}

			$task_id = $active_task['id'];
			$influencers = $jobs_model->getTaskInfluencers( $task_id );

			if ( $influencers ) {
				$influencers = $this->groupBy( $influencers, $context );
			}
		}

		return $this->render ( 'submitkeyword', [
						'model' => $model,
						'tasks' => $jobs,
						'job_data' => $main_job_data->search_term,
						'influencers' => $influencers,
						'active_task' => $active_task,
				] );
	}

	public function groupBy($array, $key) {
		$return = array();
		
		foreach($array as $val) {
			$return[$val[$key]][] = $val;
		}

		return $return;
	}

	public function actionSubmitkeywordtest() {
		Yii::trace('submit keyword test page accessed', __METHOD__);
		$model = new KeywordInputForm();
		return $this->render ( 'submitkeyword_test', [
				'model' => $model
		] );
	}

	public function actionSubmiturl() {
		Yii::trace('submit keyword test page accessed', __METHOD__);
		$model = new KeywordInputForm();
		return $this->render ( 'submiturl', [
				'model' => $model
		] );
	}

	public function actionSubmittwitter() {
		Yii::trace('submit keyword test page accessed', __METHOD__);
		$model = new KeywordInputForm();
		return $this->render ( 'submittwitter', [
				'model' => $model
		] );
	}
	
	public function actionLogin() {
		if (! \Yii::$app->user->isGuest) {
			return $this->goHome ();
		}
		
		$model = new LoginForm ();
		if ($model->load ( Yii::$app->request->post () ) && $model->login ()) {
			return $this->goBack ();
		}
		return $this->render ( 'login', [ 
				'model' => $model 
		] );
	}
	
	public function actionLogout() {
		Yii::$app->user->logout ();
		
		return $this->goHome ();
	}
	
	public function actionContact() {
		$model = new ContactForm ();
		if ($model->load ( Yii::$app->request->post () ) && $model->contact ( Yii::$app->params ['adminEmail'] )) {
			Yii::$app->session->setFlash ( 'contactFormSubmitted' );
			
			return $this->refresh ();
		}
		return $this->render ( 'contact', [ 
				'model' => $model 
		] );
	}

	public function actionAbout() {
		return $this->render ( 'about' );
	}

}