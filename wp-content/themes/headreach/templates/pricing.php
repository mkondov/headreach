<?php 

/* Template name: Pricing */

get_header();
	the_post();

    $home = trailingslashit( get_option( 'home' ) );
    $login_url = $home . 'login/';
    $signup_url = $home . 'signup/';
?>

    <section class="price-section">
        
        <header>
            <div class="row header-row clearfix">
                <div class="large-2 small-3 columns logo-column">
                    <a class="logo-wrap" href="<?php echo home_url('/') ?>" title="Logo">
                        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/logo-white.svg" alt="Logo">
                    </a>
                </div>
                <div class="large-8 small-7 columns menu-column">
                    <nav>
                        <?php
                            $args = array(
                                'container'         => false,
                                'theme_location'    => 'main-menu',
                                'menu_class'        => 'main-menu'
                            );
                            wp_nav_menu( $args );
                        ?>
                    </nav>
                </div>

                <?php include( locate_template( 'fragments/user-buttons.php' ) ); ?>
            </div>
        </header>

        <div class="row">
            <div class="small-12 columns">
                <h1>Ready to get started?</h1>
                <p>Get 10 free leads. No credit card needed. 30-day money back guarantee</p>
            </div>
        </div>
            
        <div class="row">
             <div class="small-12 medium-4 column">
                <div class="price-cube">
     
                    <div class="price-total-box">
                        <span class="total">19</span>
                        <span class="total-info">month</span>
                    </div>
                    
                    <div class="price-info">
                        <ul>
                        <li>100 Credits <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="We take away 1 credit out of your monthly allocation each time we successfully find an email address."></span></li>
                        <!-- <li>200 Search Requests <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="A search request is counted every time you do a search through our People Finder or click on Search More People. A search request returns up to 10 people. For example, if we find 100 people for HubSpot you must use 10 search requets to view all of the people."></span></li>-->
                        <li>Search from 400+ million business contacts</li>
                        <li>Advanced search</li>
                        <li>Search by person’s name</li>
                        <li>Search by domain name</li>
                        <li>Search by company</li>
                        <li>Personal social profiles</li>
                        <li>Relevant contextual data <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="Person's biography, social followers, work history and interests."></span></li>
                        <li>Company emails and social profiles</li>
                        <li>Unlimited CSV Exports</li>
                        <li>Email accuracy rates</li>
                        <li>Up-to-date data updated daily</li>
                    </ul>
                        
                        <div class="text-center">
                            <a href="<?php echo $signup_url; ?>" class="button dark large">Sign up free</a>
                        </div>
                        
                    </div>
                </div>
            </div>
                    
            <div class="small-12 medium-4 column">
                <div class="price-cube">
     
                    <div class="price-total-box">
                        <span class="total">39</span>
                        <span class="total-info">month</span>
                    </div>
                    
                    <div class="price-info">
                        <ul>
                            <li>250 Credits <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="We take away 1 credit out of your monthly allocation each time we successfully find an email address."></span></li>
                            <!-- <li>500 Search Requests <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="A search request is counted every time you do a search through our People Finder or click on Search More People. A search request returns up to 10 people. For example, if we find 100 people for HubSpot you must use 10 search requets to view all of the people."></span></li>-->
                            <li>Everything from the small plan included</li>
                            <!-- <li>Concierge email finding <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="Not finding an email? We're going to find it for you. We're really good at finding emails. Just drop us an in-app message."></span></li> -->
                        </ul>
                        
                        <div class="text-center">
                            <a href="<?php echo $signup_url; ?>" class="button dark large">Sign up free</a>
                        </div>
                    </div>
                </div>
            </div>
            
             <div class="small-12 medium-4 column">   
                <div class="price-cube">
     
                    <div class="price-total-box">
                        <span class="total">79</span>
                        <span class="total-info">month</span>
                    </div>
                    
                    <div class="price-info">
                        <ul>
                            <li>500 Credits <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="We take away 1 credit out of your monthly allocation each time we successfully find an email address."></span></li>
                            <!-- <li>1000 Search Requests <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="A search request is counted every time you do a search through our People Finder or click on Search More People. A search request returns up to 10 people. For example, if we find 100 people for HubSpot you must use 10 search requets to view all of the people."></span></li> -->
                            <li>Everything from the small and medium plans included</li>   
                        </ul>                        
                        
                         <div class="text-center">
                            <a href="<?php echo $signup_url; ?>" class="button dark large">Sign up free</a>
                        </div>
                        
                    </div>              
                </div>
            </div>   
        </div>
        
    </section>

    <section class="price-content-section">
        <div class="row">
            <div class="small-12 medium-9 columns">
                <h2>1 verified email = 1 credit</h2>
                <p>We take away 1 credit out of your monthly allocation each time we successfully find a valid email address. If we don’t find one we don’t take away any of your credits. Also, if we find a social profile, but no email for that person we keep your credits untouched.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="small-12 column">
                <h2>FAQ</h2>
                <ul>
                    <li>
                        <h3>Do the credits rollover to the next month?</h3>
                        <p>Credits don't roll over. At the beginning of your new monthly billing cycle, we'll recover your monthly email allocation.</p>
                    </li>
                    
                     </li>
                     <li>
                        <h3>Do you verify emails?</h3>
                        <p>Yes! Every email gets automatically verified in real time against a strict 7-step verification process and we never return or charge you for invalid emails! After a successful email verification, we provide a complete report explaining the status the email.</p>
                    </li>
                    <li>
                        <h3>How much does a credit costs?</h3>
                        <p>For our $19/mo plan a credit costs $0.19. For our $39/mo and $79/mo plans a credit costs just $0.15.</p>
                    </li>
                    <li>
                        <h3>Can I get a bigger plan?</h3>
                        <p>Yes. Custom plans with more credits are available. Please, <a href="mailto:contact@headreach.com">contact us</a> for more info.</p>
                   
                    <li>
                        <h3>Do I have to sign-up for a contract?</h3>
                        <p>No. HeadReach is a monthly subscriptions tool that you can cancel at any time.</p>
                    </li>
                    
                     <li>
                        <h3>Do you offer a money back guarantee?</h3>
                        <p>Yes, we offer a 30-day money back guarantee for all plans.</p>
                    </li>                     
                </ul>
            </div>
        </div>
        <div class="search-group-with-icon" style="text-align: center">
            <a target="_blank" class="button hollow" style="margin-top: 3px; border-radius: 3px" href="http://help.headreach.com/">View all questions</a>
        </div>
        
    </section>

<?php get_footer(); ?>