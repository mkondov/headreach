<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\controllers\ProspectorController;
use app\models\IDMasking;
use app\models\URLHelpers;
use app\common\components\Helpers;
use app\common\components\Paging;

$this->title = 'Archives - HeadReach';

$webPath = Yii::$app->params['webPath'];

$types = array(
	'1' => 'Search by Name',
	'2' => 'Search by Company Name',
	'3' => 'Search by Domain Name',
	'4' => 'People search by Post URL',
	'5' => 'Advanced search',
);

?>

<?php echo Yii::$app->controller->renderPartial('/layouts/header'); ?>

<!-- Main Start -->
<div class="row" data-equalizer data-equalize-on="large">
	<div class="small-12 column">

		<?php if ( empty($jobs) ) : ?>

			<div class="row align-center account-panel">
				<div class="small-11 large-9 column">
					
					<div class="row small-collapse block">
						<div class="column">
							<div class="account-panel-content">
								<p>You don't have any searches yet.</p>						
								<a href="<?php echo $webPath ?>/prospector" class="button hollow push-top-80">Create a new search</a>
							</div>
						</div>
					</div>

				</div>
			</div>

		<?php else : ?>
		
		<!-- Search Results Table Start -->
			<table class="tableWithFloatingHeader all-searches">
				<thead>
					<tr>
						<th width="40%" class="results"><span class="results-number"><?php echo $count; ?></span> Previous searches <span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="Only queries with found results are shown"></span></th>
						<th width="30%">Type of Search</th>
						<th width="10%">Results</th>
						<th width="20%">Date</th>
					</tr>
				</thead>
				<tbody>

					<?php foreach ($jobs as $job):
						$url = 'prospector/post/' . IDMasking::maskID( $job['id'] );
						$parameters = $job['parameters'];
					?>
						
						<tr>
							<td data-th="Search Query">
								<a href="<?php echo $url; ?>">"<?php echo $job['search_term'] ?>"</a>

								<div class="filters full-width in-table clearfix">
									<?php echo Yii::$app->controller->renderPartial('/partials/tags', array( 'parameters' => $parameters )); ?>
								</div>
							</td>
							<td data-th="Type of Search">
								<?php echo $types[$job['search_type']]; ?>
							</td>
						    <td data-th="Results"><?php echo number_format( $job['count'] ); ?></td>
						     <td data-th="Date">
						     	<?php echo date( 'j M Y', $job['started_at'] ); ?>
							</td>
						</tr>
						
					<?php endforeach ?>

				</tbody>
			</table>

			<?php
				$paging = new Paging();
				$paging->displayPaging( $count_with_results );
			?>

		<?php endif; ?>

	</div>	
</div>	
<!-- Main End -->