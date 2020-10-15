<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'People Finder - Results - HeadReach';

$webPath = Yii::$app->params['webPath'];

?>

<?php echo Yii::$app->controller->renderPartial('/layouts/header'); ?>

<!-- Mashead Start -->
<div class="masthead text-center">
	<div class="row">
		<div class="small-12 column">
			<h2>People Finder</h2>
			<h4>Find targeted prospects and reliable contact information</h4>
		</div>
	</div>
</div>
<!-- Masthead End -->

<!-- Main Start -->
<div class="row" data-equalizer data-equalize-on="large">

	<div class="success-progress-bar">
		<h4>Generating your report. Please wait ..</h4>
	</div>
	
	<!-- Search by Name Start -->
	<div class="small-12 large-6 column">
		<div class="block">
			<div data-equalizer-watch>
				<a href="<?php echo $webPath ?>/prospector/namesearch" class="title-with-arrow"><h2>Search People by Name</h2> <img width="15" src="<?php echo $webPath; ?>/img/icn-blue-arrow-right.svg" alt="Go to module"></a>
				<p>Find anybody's email and social profiles by searching for their personal name.</p>
			</div>

			<div class="input-group search-group-with-icon">
				<form class="search-form" method="POST" action="/app/web/task/executequery" data-search_type="1">
					<input name="search-form-field" class="input-group-field" placeholder="First name and surname" type="search">
					<a class="input-group-button button search-icon-button">Search</a>
				</form>
			</div>	
		</div>
	</div>
	<!-- Search by Name End -->
	
	
	<!-- Search by Company Start -->
	<div class="small-12 large-6 column">
		<div class="block">
			<div data-equalizer-watch>
				<a href="<?php echo $webPath ?>/prospector/companysearch" class="title-with-arrow"><h2>Search People by Company</h2> <img width="15" src="<?php echo $webPath; ?>/img/icn-blue-arrow-right.svg" alt="Go to module"></a>			
				<p>Find all people that work at a company, their real emails and social profiles For example, enter “HubSpot”.</p>
			</div>
			
			<div class="input-group search-group-with-icon">
				<form class="search-form" method="POST" action="/app/web/task/executequery" data-search_type="2">
					<input data-format="html" data-entries-url="/app/web/prospector/getcompanies" name="search-form-field" class="input-group-field autocomplete-field" placeholder="Company name" type="search">
					<a class="input-group-button button search-icon-button">Search</a>
				</form>
			</div>		
		</div>
	</div>
	<!-- Search by Company End -->
	
	<!-- Search by Website Start -->
	<div class="small-12 large-6 column">
		<div class="block">
			<div data-equalizer-watch>
				<a href="<?php echo $webPath ?>/prospector/websitesearch" class="title-with-arrow"><h2>Search People by Website</h2> <img width="15" src="<?php echo $webPath; ?>/img/icn-blue-arrow-right.svg" alt="Go to module"></a>			
				<p>Find all people that are related to a domain name. For example enter “hubspot.com”.</p>
			</div>			
			
			<div class="input-group search-group-with-icon">
				<form class="search-form" method="POST" action="/app/web/task/executequery" data-search_type="3">
					<input data-format="html" data-entries-url="/app/web/prospector/getdomains" name="search-form-field" class="input-group-field autocomplete-field" placeholder="Domain name" type="search">
					<a class="input-group-button button search-icon-button">Search</a>
				</form>
			</div>				
		</div>
	</div>
	<!-- Search by Website End -->
	
	<!-- Search by Name Start 
	<div class="small-12 large-6 column">
		<div class="block">
			
			<div data-equalizer-watch>
				<a href="#" class="title-with-arrow"><h2>Search People by Name</h2> <img width="15" src="<?php echo $webPath; ?>/img/icn-blue-arrow-right.svg" alt="Go to module"></a>
			
				<p>Find a person's contact information by searching for his name and the domain name of the company he works at. For example, "John Smith, HubSpot.com".</p>
			</div>
			
			
			<div class="input-group-two-fields clearfix">
				<input class="float-left" placeholder="Person name" type="search">
				<input class="float-left" placeholder="Domain" type="search">
				<a class="input-group-button button search-icon-button">Search</a>
				</div>
				
		</div>
	</div>
	Search by Name End -->
	
	<!-- Scan a Post Start -->
	<div class="small-12 large-6 column">
		<div class="block">
			<div data-equalizer-watch>
				<a href="<?php echo $webPath ?>/prospector/advancedsearch" class="title-with-arrow"><h2>Advanced Search</h2> <img width="15" src="<?php echo $webPath; ?>/img/icn-blue-arrow-right.svg" alt="Go to module"></a>
				<p>Use advanced search filters to discover people. For example, “editor-in-chief for Entrepreneur".</p>
			</div>
			
			<div class="input-group search-group-with-icon">
				<a style="margin-top: 3px; border-radius: 3px" href="<?php echo $webPath ?>/prospector/advancedsearch" class="button hollow">Advanced search</a>
				<!-- <form class="search-form" method="POST" action="/app/web/task/executequery" data-search_type="4">
					<input name="search-form-field" class="input-group-field" placeholder="Post URL" type="url">
					<a class="input-group-button button search-icon-button">Advanced Search</a>
				</form> -->
			</div>
		</div>
	</div>
	<!-- Scan a Post End -->

</div>	
<!-- Main End -->