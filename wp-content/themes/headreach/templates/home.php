<?php 

/* Template name: Home */

get_header();
	the_post();

	$home = trailingslashit( get_option( 'home' ) );
	$login_url = $home . 'login/';
	$signup_url = $home . 'signup/';
	$pricing_url = $home . 'pricing/';
?>
		
	<header>
	    <div class="row header-row">
	        <div class="large-2 small-3 columns logo-column">
	            <a class="logo-wrap" href="<?php echo home_url('/') ?>" title="Logo">
	                <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-dark.svg" alt="HeadReach" width="144" height="28">
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
	           
            <?php include( locate_template( 'fragments/user-buttons.php' ) ); ?>
	    </div>
	</header>

    <section class="finding-section">
        <div class="row">
            <div class="large-6 small-6 columns full-width-mobile custom-padding">
                <span class="above-title">Finding the right leads shouldn't take this long</span>
                <h1>The fastest way to find targeted leads with real emails and social profiles</h1>
                <div class="cta-box">
                    <a class="button orange" href="<?php echo $signup_url; ?>">Get 10 Free Leads</a>&nbsp;&nbsp;
                    <a class="button hollow " data-open="videomodal">► How it works</a>
                </div>
                <p class="bottom-note">No credit card required. No installation needed. 30-day money back guarantee</p>

            </div>
            <div class="img-holder">

            </div>
        </div>
    </section>

    <section class="companies-logos-section text-center">
       <h4>Helping hundreds of businesses find valuable prospects</h4>
        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-ion.png" width="88" height="52" alt="ION Group">
        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-mixergy.png" width="132" height="25" alt="Mixergy">
        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-pcloud.png" width="128" height="35" alt="pCloud">
        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-linkry.png" width="78" height="42" alt="Linkry">
        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-zimmer.png" width="144" height="28" alt="Zimmer Biomet">
    </section>

    <section class="blue-section">
        <div class="row">
            <div class="large-6 small-12 columns">
                <h2>Find the right decision makers</h2>
                <p>The biggest problem in sales prospecting is not selling the decision makers — it’s finding them in the first place!</p>
                <p>Imagine how many more deals could you close if you were always talking to the right person? By using smart targeting our Advanced Search helps you to <strong>find great prospects</strong> and <strong>save up to 20 minutes per search</strong>.</p>
            </div>
            <div class="large-6 small-12 columns"><div class="search-img-box"></div></div>
        </div>
    </section>

    <section class="search-section">
        <div class="row">
            <div class="large-12 columns text-box">
                <h2>Sales prospecting simplified</h2>
                <p>Find the right contact from over 400 million people on the web and prepare yourself with relevant data</p>
            </div>
        </div>
        <div class="profile-box-wrap">
            <div class="profile-row">
                <div class="profile-column">
                    <div class="profile-box">
                        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/features-screen.png" alt="profile" width="508" height="568" alt="HeadReach Features">
                        <div class="holder blue1">
                            <div class="dot"></div>
                            <div class="pulse"></div>
                        </div>
                        <div class="holder blue2">
                            <div class="dot"></div>
                            <div class="pulse"></div>
                        </div>
                        <div class="holder orange-dot">
                            <div class="dot"></div>
                            <div class="pulse"></div>
                        </div>
                        <div class="holder green">
                            <div class="dot"></div>
                            <div class="pulse"></div>
                        </div>
                        <div class="holder purple">
                            <div class="dot"></div>
                            <div class="pulse"></div>
                        </div>
                    </div>
                </div>
                <div class="profile-tab-column">
                    <ul class="profile-tabs">
                        <li class="date">
                            <span class="icon"></span>
                            <span class="title">Only up-to-date data</span>
                            <span class="sub-title">We use live sources, and our data gets updated on a daily basis. No outdated databases here.</span>
                        </li>
                        <li class="mail">
                            <span class="icon"></span>
                            <span class="title">80% email find success rate</span>
                            <span class="sub-title">We find your leads’ emails and show accuracy rate for each email.</span>
                        </li>
                        <li class="social">
                            <span class="icon"></span>
                            <span class="title">Social profiles and contextual data</span>
                            <span class="sub-title">We integrate with a number of APIs to provide the highest success rate on finding social profiles.</span>
                        </li>
                        <li class="data">
                            <span class="icon"></span>
                            <span class="title">Company data</span>
                            <span class="sub-title">We even find  company emails and social profiles for you so you can research your prospects better.</span>
                        </li>
                    </ul>
                </div>
                <span class="clearfix"></span>
            </div>
        </div>
    </section>

    <section class="green-section">
        
        <div class="row">
            
            <div class="small-12 medium-6 large-8 column">
                <div class="text-box">
                    <h2 class="text-left">Verified emails only</h2>
                    <p class="text-left">Say “Goodbye!” to invalid emails and your email verification software</p>
                </div>
                
                <p class="push-30">HeadReach separates low-quality email addresses from the real ones so you don’t have to bother with additional verification tools. Every email gets automatically verified in real time against a  strict 7-step verification process and we never return or charge you for invalid emails!</p>

                <p>After a successful email verification, we provide a complete report explaining the status of the email. Catch-all emails are marked as risky emails.</p>
            </div>
            
            <div class="small-12 medium-6 large-4 column">
                <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/verification-screen.png" alt="Email verification report">
            </div>
            
        </div>        
        
    </section>

    <section class="share-section">
        <div class="row align-center">
            <div class="large-12 columns text-box">
                <h3 class="text-center">What they're saying</h3>
            </div>
        </div>

        <div class="row align-center align-center testimonial">
            <div class="small-4 medium-2 large-1 column">
                <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/andrew.jpg" class="testimonial-avatar" width="108" height="108" alt="Andrew Warner photo">
            </div>
            
            <div class="small-12 medium-9 large-7 column">
                “Powerful way to find email addresses! We use it at Mixergy to find contact info for potential guests.”<br>
                <small>— Andrew Warner, creator, Mixergy</small>
            </div>
        </div>
        
        <div class="row align-center align-center testimonial">
            <div class="small-4 medium-2 large-1 column">
                <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/fraser.jpg" class="testimonial-avatar" width="108" height="108" alt="Fraser McCulloch photo">
            </div>
            
            <div class="small-12 medium-9 large-7 column">
                "First impressions are f**king amazing!
                    
                I am looking to target new clients and I am finding about 90% email addresses of the people and websites I am searching for."<br>
                <small>—  Fraser McCulloch, owner, Platonik</small>
            </div>
        </div>
        
        <div class="row align-center align-center testimonial">
            <div class="small-4 medium-2 large-1 column">
                <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/pax.jpg" class="testimonial-avatar" width="108" height="108" alt="Pax Franchot photo">
            </div>
            
            <div class="small-12 medium-9 large-7 column">
                “I have 2 interviews next week based on jobs I went after using emails I pulled off HeadReach. When combined with being one of the first applicants, sending a personal email is wildly more effective than just applying on the website.”<br>
                <small>— Pax Franchot, award-winning creative director</small>
            </div>
        </div>
        

    </section>

    <section class="leads-bar-section">
        <div class="row">
            <div class="small-12 medium-6 large-8 columns">
                <h4>Start now with 10 free leads</h4>
                <p>Put an end to the prospecting headache</p>
            </div>
            <div class="small-12 medium-6 large-4 columns cta-footer">
                <a href="<?php echo $signup_url ?>" class="button orange">Get 10 free leads</a>&nbsp;&nbsp;
                <a class="button hollow" data-open="videomodal">► How it works</a>
                <p class="cta-note">No credit card required. No software installations are needed.</p>
                
                <div class="large reveal" id="videomodal" data-reveal>
                    <iframe width="100%" height="100%" src="https://www.youtube.com/embed/YU1mcbghUe0?rel=0&amp;controls=1&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>
                </div>

            </div>
        </div>
    </section>

<?php get_footer(); ?>