<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Submit Keyword';

?>

<?php  if ( $_SERVER['REMOTE_ADDR'] == '78.90.149.9' ) : ?>
<?php  // if ( $_SERVER['REMOTE_ADDR'] == '93.152.145.119' ) : ?>

<!-- Header Start -->
<header class="header clearfix">
  	
  	<div class="float-left">
		<form class="query-form" method="POST" action="/web/task/executequery">
		  	<a href="#"><img src="http://headreach.info/web/img/logo-black.svg" alt="HeadReach" width="100" class="logo float-left"></a>
		  	
		  	<div class="input-group float-left hide-for-small-only">
	        	<input class="input-group-field search-field" type="text" name="url" placeholder="Enter your website">
				<input type="hidden" name="_csrf" value="<?php echo Yii::$app->request->getCsrfToken() ?>" />
		        <div class="input-group-button">
					<input type="submit" class="hollow button" value="New report" data-tooltip title="3 reports left. This will create a new report.">
		        </div>
		     </div>
		</form>
  	</div>
  	
  	<div class="float-right">
	  	
	  	<ul class="menu-not-signed">
		  	
		  	<li>
		  		Sign up now to save and export this report
			</li>
		  	<li>
		  		<a class="button hollow" href="../promo-site/login.html">Login</a>
		  	</li>
		  	<li>
		  		<a class="button brown" href="../promo-site/pricing.html">Sign Up</a>
		  	</li>
	  	</ul>
	  	
  	</div>
     
</header>
<!-- Header End -->

<?php endif; ?>
	
<!-- Masthead Start -->
<div class="masthead">
	<div class="row full-width small-collapse">
  		<div class="small-12 column medium-10">
  			<!-- <h3 class="masthead-title push-top-20">50 Ways To Increase The Value Of Your Home</h3> -->
  			<h3 class="masthead-title push-top-20">Outreach report for <?php echo $job_data; ?></h3>
  			<ul class="masthead-meta subpixel">
	  			<!-- <li>22 May 2016</li> -->
	  			<li>300 opportunities</li>
	  			<li>20 channels</li>
  			</ul>
  		</div>
  		
  		<div class="small-12 column medium-2">
	  		<a href="#" class="button-icon button hollow white hide-for-small-only expanded"><img alt="Download" src="http://headreach.info/web/img/download.svg" width="15">Excel</a>
  		</div>
	</div>  		
</div>
<!-- Masthead End -->
	
<!-- Main Start -->
<div class="main">

	<div class="off-canvas-wrapper">
     
     	<div class="off-canvas-wrapper-inner" data-off-canvas-wrapper> 
	          
			<!-- Left Sidebar Mobile Start -->  
			<div class="off-canvas position-left" id="offCanvasLeft" data-off-canvas>

				<?php echo Yii::$app->controller->renderPartial('/layouts/sidebar-left', array('sidebar_items' => $tasks ));  ?>
				
			</div>
			<!-- Left Sidebar Mobile End -->
			
			<div class="off-canvas-content clearfix" data-off-canvas-content>
	            
            	<div class="row full-width small-collapse clearfix">
  	
  				  	
				  	<!-- Left Sidebar Desktop Start -->
				  	<div class="left-sidebar shrink columns show-for-large left-sidebar-collapse" id="channels-collapse-trigger" data-toggler="left-sidebar-collapsed">
					  	
					  	<!-- Channels Collapse Start -->
					  	<a href="#" class="channels-collapse-trigger" data-toggle="channels-collapse-trigger">
						  	Channels
						  	<span class="menu-sandwich"></span>
					  	</a>
					  	<!-- Channels Collapse End -->

					  	<?php echo Yii::$app->controller->renderPartial('/layouts/sidebar-left', array('sidebar_items' => $tasks ));  ?>
				  	</div>
				  	<!-- Left Sidebar End -->
				  	
				  	
				  	<!-- Content Wrapper Start -->
				  	<div class="columns">
					  	
					  	<div class="row full-width small-collapse">
						  			  	
						  	<!-- Content Start -->
						  	<div class="columns content" id="content">
							  	  	
							  	<!-- Channel Header -->
							  	<div class="channel-header clearfix">
								  	
								  	<div class="float-left">
								  		<button type="button" class="all-channels-button hide-for-large float-left" data-toggle="offCanvasLeft">All</button>
								  		<img src="http://headreach.info/web/img/channels-icons/mentions.svg" alt="Post Mentions" class="float-left channel-header-image">
								  		<h4 class="subheader float-left channel-header-title">Page Mentions</h4>
								  	</div>
								  	
								  	<div class="float-right">
									  	<a href="#" class="guide-link subpixel hide-for-small-only" data-tooltip title="Usage guide and tips for Page mentions"><img src="http://headreach.info/web/img/book.svg" alt="Guide" width="25"> Guide</a>
								  	</div>
								  	
							  	</div>

							  	<?php
							  		$args = array(
							  			'influencers' => $influencers,
							  			'active_task' => $active_task,
							  		);
							  		echo Yii::$app->controller->renderPartial('/layouts/entries-list', $args);
							  	?>
							  	
						  	</div>
						  	<!-- Content End -->
						  	
						  	<!-- Right Sidebar Desktop Start -->
						  	<?php echo Yii::$app->controller->renderPartial('/layouts/sidebar-people');  ?>
						  	<!-- Right Sidebar Desktop End -->
						  	
					  	</div>
					
				  	</div>
					<!-- Content Wrapper End -->
					
				</div>
	            
			</div>
		</div>
    </div>
	  	
</div>
<!-- Main End -->
	
	
<!-- Pop Ups Start -->

<!-- Sniper Search Start -->
<div class="reveal text-center" id="sniperSearchModal" data-reveal>
	
	<h3 class="subheader push-top-20">Find the perfect contact at any organization 
	with our advanced prospecting search</h3>
	
	<!-- Search Start -->
	<div class="callout clearfix push-top-30">
		<h4 class="subheader sniper-search-headline">Find <span>content managers</span> at <span>Microsoft</span></h4>
		
		<span class="sniper-search-price push-top-30 push-bottom-30">$0.78</span>
		
		<a href="#" class="button large">Place order</a>
	</div>
			
	<p><small>You only get charged if we find a match.<br> By clicking “Place order” you authorize us to charge your account.</small></p>
	
	<!-- Search End -->
	
 	<!-- Close Button Start -->
  	<button class="close-button" data-close aria-label="Close modal" type="button">
		<span aria-hidden="true">&times;</span>
	</button> 
	<!-- Close Button End -->

</div>
<!-- Sniper Seach End -->

<!-- Pop Ups End -->