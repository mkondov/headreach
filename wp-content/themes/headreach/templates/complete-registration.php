<?php 

/* Template name: Finish Registration Page */

get_header();
	the_post(); ?>

    <?php get_template_part( 'fragments/header-app' ); ?>
    
    <!-- Main Start -->
    <div class="row align-center account-panel">
        
        <!-- Billing Start -->
        <div class="small-11 large-9 column">
            <div class="row small-collapse block">
                <div class="woocommerce">

                    <?php 
                         $current_user = wp_get_current_user();
                    ?>

                    <div class="column">
                        <div class="account-panel-content">
                            <div class="woocommerce-MyAccount-content">
                                <h3><?php the_title(); ?></h3>
                                <p>Almost done! We've sent a confirmation email to <strong><?php echo $current_user->user_email; ?></strong>. Click the link in the email to confirm this address.</p>

                                <!-- <p>Hello <strong><?php echo $current_user->user_login; ?></strong>! Thank you for signing up with HeadReach. We've sent a confirmation email to your postbox. Please confirm it so you could use the full capabilities of our platform.<p/> -->
                                <p style="font-weight: 13px; padding-top: 30px;">Can't find that email - <a href="#">resend confirmation</a> or <a href="#">contact us</a>.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- Billing End -->

    </div>  
    <!-- Main End -->

<?php get_footer(); ?>