<?php get_header(); 
	the_post(); ?>
    	
    <?php
    	$home = trailingslashit( get_option( 'home' ) );
		$login_url = $home . 'login/';
		$signup_url = $home . 'signup/';

		$logged_in = is_user_logged_in();
		$button = '<a href="'. $login_url .'" class="login-button">Log in</a>';

		if ( $logged_in ) {
			$current_user = wp_get_current_user();
			$button = '<a class="login-button" href="'. get_option( 'home' ) .'/app/web/prospector">Dashboard</a>';
		}
	?>	

    <header>
        <div class="row header-row">
            <div class="large-2 small-3 columns logo-column">
                <a class="logo-wrap" href="<?php echo $home; ?>" title="Logo">
                    <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-dark.svg" alt="Logo">
                </a>
            </div>
            <div class="large-7 small-6 columns menu-column">
                <nav>
                    <?php
						$args = array(
							'container' 		=> false,
							'theme_location' 	=> 'main-menu',
							'menu_class' 		=> 'main-menu'
						);
						wp_nav_menu( $args );
					?>
                </nav>
            </div>
            
            <div class="large-3 small-3 columns login-column show-for-large">
                <a href="<?php echo $signup_url; ?>" class="button orange hollow small">Signup</a>
                <?php echo $button; ?>
            </div>
        </div>
    </header>

	<div class="row align-center">
		<div class="small-12 medium-10 large-8 column">
			<h2><?php the_title(); ?></h2>
			<?php the_content(); ?>
		</div>
	</div>

<?php get_footer(); ?>