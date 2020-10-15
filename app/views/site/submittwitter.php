<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Submit Keyword';
$this->params ['breadcrumbs'] [] = $this->title;
// $this->registerJsFile('@web/js/keyword_submit.js', ['position' => \yii\web\View::POS_READY]);

// $this->registerJsFile ( 'js/keyword_submit.js' );
$this->registerJsFile ( Yii::$app->request->BaseUrl . '/js/twitter_submit.js', [ 
		'depends' => [ 
				yii\web\JqueryAsset::className () 
		] 
] );
?>

<div class="site-login">
	<h1><?= Html::encode($this->title) ?></h1>

	<p>Please fill out the following fields to login:</p>

	<form id="keyword-form" class="form-horizontal">
		<input type="hidden" name="_csrf"
			value="<?=Yii::$app->request->getCsrfToken()?>" />
		<div class="form-group required has-error">
			<label class="col-lg-1 control-label" for="input-keyword">Keyword</label>
			<div class="col-lg-3">
				<input id="input-keyword" class="form-control" name="keyword"
					autofocus="" type="text">
			</div>
			<div class="col-lg-8">
				<p class="help-block help-block-error">Keyword cannot be blank.</p>
			</div>
		</div>

		<div class="form-group">
			<div class="col-lg-offset-1 col-lg-11">
				<button type="submit" class="btn btn-primary" id="keyword-button">Submit</button>
			</div>
		</div>

	</form>

	
	<div class="col-lg-offset-1" style="color: #999;" id="keyword-wait">
		wait a sec...
	</div>

	<table class="table table-striped table-bordered col-lg-offset-1" style="color: #999;"
		id="keyword-results-table">
		<th>company</th>
		<th>company_social</th>
		<th>title</th>
		<th>full_name</th>
		<th>email</th>
		<th>contact_info</th>
		<th>person_social</th>
		<tr>

		</tr>

	</table>
</div>