<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Loading Report';

?>

<!-- Header Start -->
<header class="header clearfix">
  	
  	<div class="row small-collapse">
	  	
	  	<div class="small-12 column">
		  	
	  		<div class="float-left">
			  	<a href="#"><img src="http://headreach.info/web/img/logo-black.svg" alt="HeadReach" width="100" class="logo float-left"></a>
			  	
			  	<div class="input-group float-left hide-for-small-only">
			        <input class="input-group-field" type="text" placeholder="Your website, keyword or URL">
			        <div class="input-group-button">
			          <input type="submit" class="hollow button" value="New report" data-tooltip title="3 reports left. This will create a new report.">
			        </div>
			     </div>
		  	</div>
	  	
	  		<div class="float-right">
		  	
			  	<ul class="menu dropdown header-nav subpixel" data-dropdown-menu>
				  	
				  	<li class="all-reports">
				  		<a href="all-reports.html">All reports</a>
				  	</li>
				  	
				  	<li class="hide-for-medium-only">
				  		<a href="#">Help</a>
				  	</li>
				  	
				  	<li class="show-for-large">
				  		<a href="#">Blog</a>
				  	</li>
				  	
				  	<li>
						<a class="avatar" style="background-image: url(http://headreach.info/web/img/michele.jpg)">Michele Finotto</a>
						<ul class="menu">
							<li><a href="#">Settings</a></li>
							<li><a href="#">Subscription</a></li>
							<li><a href="#">Logout</a></li>
			          	</ul>
				  	</li>
			  	</ul>
			  	
		  	</div>
	  		
	  	</div>
  	</div>
     
</header>
<!-- Header End -->
	
<!-- Masthead Start -->
<div class="masthead">
	<div class="row small-collapse align-justify">
  		<div class="small-12 column medium-3">
  			<h3 class="masthead-title push-top-20">All Reports</h3>
  			<ul class="masthead-meta subpixel hide-for-small-only">
	  			<li>5 reports created</li>
	  			<li>3,230 opportunities found</li>
  			</ul>
  		</div>
  		
  		<div class="small-12 column medium-9 clearfix">
	  		
	  		<div class="row full-width small-collapse align-justify">
		  		<div class="column medium-5 large-8">
		  			<a href="#" class="button-icon button hollow white hide-for-small-only float-right"><img src="http://headreach.info/web/img/arrow-up-circle.svg" width="15" alt="Upgrade your account"> Get more reports</a>
		  		</div>
		  		
		  		<div class="column small-12 medium-6 large-3 report-count" data-tooltip title="Report count will reset in 7 days time.">
			  		<span class="report-count-number">8</span> Reports available
					<div class="white progress" role="progressbar" tabindex="0" aria-valuenow="8" aria-valuemin="0" aria-valuetext="8 reports" aria-valuemax="10">
						<div class="progress-meter" style="width: 80%"></div>
					</div>
						
					<span class="masthead-meta subpixel">Out of 10 reports a month</span>
			  		
			  	</div>
	  		</div>
	  		
  		</div>
		
	</div>	
</div>
<!-- Masthead End -->
	
<!-- Main Start -->
<div class="main">
	            
	<div class="row small-collapse clearfix">			  	
	  	<!-- Content Start -->
	  	<div class="columns small-12 content" id="content">
			
		  	<!-- Report Loading Start -->			  	
			<div class="row small-uncollapse align-center text-center push-top-40">
				<div class="small-11 large-9 column">
					
			  		<h4 class="subheader">Creating your outreach report for:</h4>
			  		<h2>50 Ways to Increase the Value of Your Home</h2>
			  		
			  		<h4 class="subheader">We are currently building your report. Once it is ready you would get an email notification. Please use the link below in order to access your awesome outreach data.</h4>

			  		<?php 
			  			$url = 'http://headreach.info/web/site/submitkeyword/?id=' . $masked_id;
			  		?>

			  		<br />
			  		<p><a target="_blank" class="blue hollow button" href="<?php echo $url ?>"><?php echo $url ?></a></p>
			  
					<div class="success progress push-bottom-40 push-top-40" role="progressbar" tabindex="0" aria-valuenow="40" aria-valuemin="0" aria-valuetext="40 percent" aria-valuemax="100">
						<div class="progress-meter" style="width: 40%"></div>
					</div>
						
					<h4 class="subheader push-top-20">Trust us, it’s worth it.</h4>
					
					<a href="all-reports.html" class="button hollow push-top-80">Back to all reports</a>
					<p>It’s safe to leave this page. We’re creating your report in the background.</p>

				 </div>
			 </div>
			 <!-- Report Loading End -->
		  	
	  	</div>
	  	<!-- Content End -->				
	</div>
		  	
</div>
<!-- Main End -->