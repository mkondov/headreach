<?php get_header(); ?>
	
	<!-- Masthead Start -->
	<div class="dark bottom bg-gray-dark">
	
		<!-- Header Start -->
		<header class="header clearfix">
				
			<div class="row">
				<div class="small-12 column">

					<?php get_template_part( 'fragments/header-navigation' ) ?>

				</div>
			</div>						
		</header>
		<!-- Header End -->	
					
	</div>
	<!-- Mast Head End -->

	<!-- White Section Start -->
	<div class="row">
		<div class="small-12 column text-center">

			<?php crb_the_title('<h2 class="push-60">', '</h2>'); ?>
			<br />

			<?php get_template_part( 'loop' ); ?>
			
		</div>
	</div>
	<!-- White Section End -->

<?php get_footer(); ?>