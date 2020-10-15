<?php 

/* Template name: Account Page */

get_header();
	the_post(); ?>

    <?php get_template_part( 'fragments/header-app' ); ?>
    
    <!-- Main Start -->
    <div class="row align-center account-panel">
        
        <!-- Billing Start -->
        <div class="small-11 large-9 column">
            <div class="row small-collapse block">
                <?php the_content(); ?>
            </div>
        </div>
        <!-- Billing End -->

    </div>  
    <!-- Main End -->

<?php get_footer(); ?>