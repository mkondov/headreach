<?php
    $home = trailingslashit( get_option( 'home' ) );
    $login_url = $home . 'login/';
    $signup_url = $home . 'signup/';

    $current_user = wp_get_current_user();
    $g_image = get_avatar_url( $current_user->ID );

    $credits = getCredits();

    $has_sub = false;
    if ( function_exists('wcs_user_has_subscription') ) {
        $has_sub = wcs_user_has_subscription( '', '', 'active' );
    }
?>

<!-- Top Nav for Medium Screen Start -->
<header class="header subpixel hide-for-small-only">
    
    <div class="row small-collapse align-justify">
        
        <!-- Left Section Start -->
        <div class="column shrink">
            <ul class="header-nav">
                <li>
                    <a href="<?php echo $home; ?>app/web/prospector" class="logo">
                        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/app/logo.svg" alt="HeadReach" title="HeadReach">
                    </a>
                </li><li>
                    <a href="<?php echo $home; ?>app/web/prospector" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Find people">
                        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/app/icn-search-white.svg" width="20" alt="Search">Search
                    </a>
                </li><li>
                    <a href="<?php echo $home; ?>app/web/contacts" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Your contact book">
                        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/app/icn-person.svg" width="19" alt="Contacts">Contacts
                    </a>
                </li><li>
                    <a href="<?php echo $home; ?>app/web/searches" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Log with your previous searches">
                        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/app/icn-notepad.svg" width="19" alt="Contacts">Log
                    </a>
                </li><?php if ( !$has_sub ) : ?><li><a href="<?php echo $home ?>/my-account/subscription/" class="upgrade-nav-button show-for-large" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Get more credits"><span>★</span> Upgrade now</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <!-- Left Section End -->
        
        <!-- Right Section Start -->
        <div class="column shrink">
            <ul class="menu dropdown float-right header-nav" data-dropdown-menu>
                <li class="progress-wrapper">
                    <a href="<?php echo $home; ?>my-account/subscription/" class="credits" data-tooltip  data-disable-hover="false" tabindex="1" title="<?php echo $credits['left'] ?> credits left. Upgrade your plan to get more credits">
                        <div class="clearfix">
                            <span class="float-left'">Credits</span>
                            <span class="float-right"><span class="highlight"><?php echo $credits['used'] ?></span> used  <span class="divider">/</span> <span class="highlight"><?php echo $credits['total'] ?></span> total</span>
                        </div>
                        <div class="progress" role="progressbar" tabindex="0" aria-valuenow="<?php echo $credits['percentage_used'] ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-meter" style="width: <?php echo $credits['percentage_used'] ?>%"></div>
                        </div>
                    </a>
                </li>

                <li class="support-nav-button">
                    <a target="_blank" href="http://help.headreach.com/" data-tooltip  data-disable-hover="false" tabindex="1" title="Help"><span class="badge info-white"></span></a>
                </li>
                
                <li>
                    <a>
                        <span class="avatar" style="background-image: url(<?php echo $g_image; ?>)"></span>
                        <span class="hide-for-medium-only"><?php echo $current_user->user_firstname; ?></span></a>
                        
                    <ul class="menu">
                        <li>
                            <a href="<?php echo $home; ?>app/web/searches">Search Log</a>
                        </li>
                        <li>
                            <a href="<?php echo $home; ?>my-account">Account Settings</a>
                        </li>
                        <li>
                            <a href="<?php echo $home; ?>my-account/subscription/">Subscription <span class="button orange hollow tiny subpixel upgrade-button-in-dropdown">Upgrade</span></a>
                        </li>
                        <li>
                            <a href="<?php echo $home; ?>my-account/customer-logout/">Logout</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <!-- Right Section End -->
    
    </div>
    
</header>
<!-- Top Nav for Medium Screen End -->   

<!-- Top Nav for Small Screen Start -->
<header class="header hide-for-medium">
    
    <div class="row small-collapse align-justify">
        
        <!-- Left Section Start -->
        
        <div class="column shrink">
            <ul class="header-nav">
                <li>
                    <a href="<?php echo $home; ?>app/web/prospector" class="logo">
                        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/app/logo.svg" alt="HeadReach" title="HeadReach">
                    </a>
                </li><li>
                    <a href="<?php echo $home; ?>app/web/searches" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Find people">
                        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/app/icn-search-white.svg" width="20" alt="Search">
                    </a>
                </li><li>
                    <a href="<?php echo $home; ?>app/web/contacts" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Your contact book">
                        <img src="<?php bloginfo( 'stylesheet_directory'); ?>/images/app/icn-search-white.svg" width="19" alt="Contacts">
                    </a>
                </li>
            </ul>
        </div>
        <!-- Left Section End -->            
        
        <!-- Right Section Start -->
        <div class="column shrink">
            <ul class="menu dropdown header-nav" data-dropdown-menu>
                <li>
                    <a><span class="avatar" style="background-image: url(<?php echo $g_image; ?>)"></span></a>
                    <ul class="menu">
                        <li>
                            <a href="#">Search Log</a>
                        </li>
                        <li>
                            <a href="#">Account Settings</a>
                        </li>
                        <li>
                            <a href="#">Billing</a>
                        </li>
                        <li>
                            <a href="#">Subscription <span class="button orange hollow tiny subpixel upgrade-button-in-dropdown">Upgrade</span></a>
                        </li>
                        <li>
                            <a href="<?php echo $home; ?>my-account/customer-logout/">Logout</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <!-- Right Section End -->        
    </div>
    
</header>
<!-- Top Nav for Small Screen End -->