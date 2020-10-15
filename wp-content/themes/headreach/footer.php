	<?php 
		$home = trailingslashit( get_option( 'home' ) );
		$spam_url = $home . 'anti-spam-policy/';
	?>

	<?php if ( crb_get_meta( '_crb_show_footer' ) ) : ?>
	
		<footer>
	        <div class="row">
	            <div class="large-6 small-4 columns">
	                <a href="<?php echo home_url('/') ?>" title="Company">
	                    <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-dark.svg" width="110" height="21" alt="footer-logo">
	                </a>
	            </div>
	            <div class="large-6 small-8 columns">
	                <p class="mail">
	                	<a href="<?php echo $spam_url; ?>">Anti-spam policy</a>
	                	<span>|</span>
	                	<a href="mailto:contact@headreach.com">contact@headreach.com</a>
	                	<a target="_blank" href="https://www.facebook.com/headreach/" class="face"></a>
	                	<a target="_blank" href="https://twitter.com/headreachapp" class="twit"></a>
	                </p>
	            </div>
	        </div>
	    </footer>
	    
	<?php endif; ?>

	<?php wp_footer(); ?>

   </body>
</html>