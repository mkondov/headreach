<?php 

/* Template name: Signup */

get_header();
	the_post();

	$home = trailingslashit( get_option( 'home' ) );
    $login_url = $home . 'login/';
    $signup_url = $home . 'signup/';
?>

	<div class="row custom-row">
        <div class="large-12 columns">
            <div class="text-center">
               <a href="<?php echo home_url('/') ?>"> <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-dark.svg" alt="logo" width="170"> </a>
            </div>
            <div class="sign-up-box">
                <div class="row">
                    
                    <div class="small-12 large-7 columns form-box form-holder">     
                        <form action="#" method="post" class="form-new-members">
                        	<div class="callout"></div>

                            <label>First name</label>
                            <input type="text" name="first_name" placeholder="John" />

                            <label>Last name</label>
                            <input type="text" name="last_name" placeholder="Smith" />

                            <label>Email <small>(please use your company email address)</small></label>
                            <input type="text" name="email_address" placeholder="mail@company.com" />

                            <label>Phone <small>(phone number required)</small></label>
                            <input type="tel" id="phone" type="tel" name="phone_number" placeholder="Please use numeric numbers only" />

                            <label>Password</label>
                            <input type="password" name="user_password" placeholder="password" />

                            <button class="button orange" type="submit">Start Using HeadReach</button>

                            <input type="hidden" name="redirect-url" value="" />
							<?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>

							<div class="overlay"></div>
                        </form>
                    </div>

                    <div class="small-12 large-5 columns avatar-info-box">
                        <p class="quote">“HeadReach works fast! I can find contacts easy, and I’m able to save over 3 hours of my time each and every day when I'm doing outreach.”</p>
                        <div class="text-center">
                            <div class="avatar-circle-box"></div>
                        </div>
                        <p class="name">Ivan Dimitrov</p>
                        <p class="position">Marketing Manager, pCloud</p>
                    </div>
                </div>
            </div>
            <p class="form-note">Already have an account? <a href="<?php echo $login_url; ?>">Log In</a></p>
        </div>
    </div>	

<?php get_footer(); ?>