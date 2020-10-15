<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\controllers\ProspectorController;
use app\models\IDMasking;
use app\common\components\Helpers;

$this->title = 'People Finder - Results - HeadReach';

$webPath = Yii::$app->params['webPath'];
$websiteURL = Yii::$app->params['websiteURL'];
$socials_map = ProspectorController::getSocials();

$credits = Helpers::getCredits();

$search_term = $main_job_data->search_term;
$search_type = $main_job_data->search_type;

switch ($search_type) {
	case 1:
		$search_type_name = 'namesearch';
		$label = 'Name'; 
		break;
	case 2:
		$search_type_name = 'companysearch';
		$label = 'Company name'; 
		break;
	case 3:
		$search_type_name = 'websitesearch';
		$label = 'Domain name'; 
		break;
	case 4:
		$search_type_name = 'postscan';
		$label = 'Post URL';
		break;
	case 5:
		$search_type_name = 'advancedsearch';
		$label = 'Advanced search';
		break;
}

?>

<script type="text/javascript">
	var do_poll = true;
	var maskedID = "<?php echo $masked_job_id; ?>";
</script>

<?php
	echo Yii::$app->controller->renderPartial('/layouts/header');

	$data = [
		'active_tab' => $search_type_name
	];
	echo Yii::$app->controller->renderPartial('/prospector/sub-navigation', $data);
?>

<!-- Mashead Start -->
<div class="masthead">
	<div class="row">
		<div class="small-12 column">

			<?php if ( $search_type == 5 ) : ?>

				<h4 class="no-bottom-margin">
					<span class="module-title">Advanced search</span>
					<span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="Find people using advanced search parameters."></span>
				</h4>

			<?php else : ?>

				<h4>
					<a href="<?php echo $webPath ?>/prospector">Search</a>:
					<span class="module-title">By <?php echo $label; ?></span>
					<span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="<?php echo $desc; ?>"></span>
				</h4>
				
				<!-- Module Search Start -->
				<form class="search-form" method="POST" action="/app/web/task/executequery" data-search_type="<?php echo $search_type; ?>">
					<div class="input-group module-search">				
						<input name="search-form-field" class="input-group-field" value="<?php echo ( $search_term ? $search_term : '' ); ?>" placeholder="<?php echo $label; ?>" type="search">
						<a class="input-group-button button orange subpixel">Search</a>
					</div>
				</form>
				<!-- Module Search End -->

				<div class="success-progress-bar in-results">
					<h4>Generating your report. Please wait ..</h4>
				</div>

			<?php endif; ?>
				
		</div>
	</div>
</div>
<!-- Masthead End -->

<!-- Main Start -->
<div class="row">
	
	<div class="small-12 column text-center">
		<div class="loading large-bar">
			<div class="loading-bars-wrapper">
				<div class="loading-bar"></div>
				<div class="loading-bar"></div>
				<div class="loading-bar"></div>
				<div class="loading-bar"></div>
				<div class="loading-bar"></div>
			</div>
			<div class="text">Finding people ...</div>
		</div>
	</div>

</div>