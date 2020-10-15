<?php get_header();
	the_post(); ?>

	<?php get_template_part( 'fragments/header-app' ); ?>
    
    <!-- Main Start -->
    <div class="row align-center account-panel">
        
        <!-- Billing Start -->
        <div class="small-11 large-9 column">
            <div class="row small-collapse block">
            	<div class="woo-wrapper">
                	
                	<?php wc_get_template_part( 'content', 'single-product' ); ?>

                </div>
            </div>                
        </div>
        <!-- Billing End -->        

    </div>  
    <!-- Main End -->

<?php get_footer(); ?>