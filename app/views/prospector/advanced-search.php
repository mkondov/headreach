<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'People Finder - Results - HeadReach';

$webPath = Yii::$app->params['webPath'];
$active_page = Yii::$app->controller->action->id;

?>

<?php
	echo Yii::$app->controller->renderPartial('/layouts/header');

	$data = [
		'active_tab' => $active_page
	];
	echo Yii::$app->controller->renderPartial('/prospector/sub-navigation', $data);
?>

<!-- Mashead Start -->
<div class="masthead">
	<div class="row">

		<div class="small-12 column">
			<h4 class="no-bottom-margin"><span class="module-title">Advanced Search</span> <a target="_blank" class="button white hollow support" href="https://headreach.helpdocs.io/article/xjssgf6PGR-advanced-search-make-targeted-searches"><span>►</span> Video tour</a></h4>		
		</div>

		<div class="success-progress-bar">
			<h4>Generating your report. Please wait ..</h4>
		</div>

	</div>
</div>
<!-- Masthead End -->

<div class="row">

	<!-- Filters Start -->
	<div class="small-12 medium-10 large-7 float-center column">
		<div class="block no-padding">
			<div class="filters full-page">

				<?php 
					$args = array( 'main_job_data' => array() );
					echo Yii::$app->controller->renderPartial('/prospector/filter', $args);
				?>

			</div>
		</div>
	</div>
	<!-- Filters End -->
			
</div>