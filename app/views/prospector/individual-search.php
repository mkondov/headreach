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
			<h4><a href="<?php echo $webPath ?>/prospector">Search</a>: <span class="module-title">By <?php echo $label; ?></span></h4>
			
			<!-- Module Search Start -->
			<form class="search-form" method="POST" action="/app/web/task/executequery" data-search_type="<?php echo $type; ?>">
				<div class="input-group module-search">

					<?php if ( $active_page == 'companysearch' ) : ?>

						<input data-format="html" data-entries-url="/app/web/prospector/getcompanies" name="search-form-field" class="input-group-field autocomplete-field" placeholder="<?php echo $label; ?>" type="search">

					<?php elseif ( $active_page == 'websitesearch' ) : ?>

						<input data-format="html" data-entries-url="/app/web/prospector/getdomains" name="search-form-field" class="input-group-field autocomplete-field" placeholder="<?php echo $label; ?>" type="search">

					<?php else : ?>

						<input name="search-form-field" class="input-group-field" placeholder="Search by <?php echo $label; ?>" type="search">

					<?php endif; ?>
					
					<a class="input-group-button button orange subpixel">Search</a>

				</div>
			</form>
			<!-- Module Search End -->
				
		</div>

		<div class="success-progress-bar">
			<h4>Generating your report. Please wait ..</h4>
		</div>

	</div>
</div>
<!-- Masthead End -->