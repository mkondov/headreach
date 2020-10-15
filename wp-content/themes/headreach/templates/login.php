<?php 

/* Template name: Login */

get_header();
	the_post();

	$home = trailingslashit( get_option( 'home' ) );
    $login_url = $home . 'login/';
    $signup_url = $home . 'signup/';
?>

	<div class="row">
	    <div class="large-6 small-12 medium-8 float-center columns">
	        
	        <div class="text-center">
	           <a href="<?php echo home_url('/') ?>"> <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-dark.svg" alt="logo" width="170"> </a>
	        </div>
	   
			<div class="sign-up-box form-holder">

	            <form action="#" method="post" class="form-sign-in">
					<div class="callout"></div>

	                <label>Email</label>
	                <input id="email_address" type="text" name="email_address" placeholder="mail@mail.com" />

	                <label>Password</label>
	                <input id="user_password" type="password" name="user_password" placeholder="password" />
	                
	                <small><a href="https://headreach.com/my-account/lost-password/">Forgotten password?</a></small>

	                <button class="button orange" type="submit">Login</button>

	                <input type="hidden" name="redirect-url" value="" />
					<?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>

					<div class="overlay"></div>
	            </form>
			</div>
			
	    </div>
	</div>
	<div class="row">
	    <div class="large-6 small-12 medium-8 float-center columns">
			<p class="form-note">Don't have an account? <a href="<?php echo $signup_url; ?>">Sign Up</a></p>
	    </div>
	</div>

<?php get_footer(); ?>