<?php
	$logged_in = is_user_logged_in();
	$button = '<a href="'. $login_url .'" class="login-button">Log in</a>';

	if ( $logged_in ) {
		$current_user = wp_get_current_user();
		$button = '<a class="login-button" href="'. get_option( 'home' ) .'/app/web/prospector">Dashboard</a>';
	}
?>

<?php if ( is_front_page() AND !$logged_in ) : ?>

	<div class="large-3 small-3 columns login-column show-for-large">
	    <a href="<?php echo $signup_url; ?>" class="button orange hollow small">Signup</a>
	    <?php echo $button; ?>
	</div>

<?php else : ?>

	<div class="large-2 small-2 columns login-column text-right">
		<?php echo $button; ?>
	</div>

<?php endif; ?>