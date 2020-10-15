<?php
	use app\common\components\Helpers;

	$webPath = Yii::$app->params['webPath'];
	$websiteURL = Yii::$app->params['websiteURL'];
	$active_page = Yii::$app->controller->id;

	$session_info = Helpers::getSessionData();
	$first_name = $session_info['current-user']['user_first_name'];
	$avatar = $session_info['current-user']['avatar'];
	$has_sub = $session_info['current-user']['has_sub'];

	$credits = Helpers::getCredits();
	$searches = Helpers::getSearches();
?>

<!-- Top Nav for Medium Screen Start -->
<header class="header subpixel hide-for-small-only">
	
	<div class="row small-collapse align-justify">
		
		<!-- Left Section Start -->
		<div class="column shrink">
			<ul class="header-nav">
				<li>
					<a href="<?php echo $webPath ?>/prospector" class="logo">
						<img src="<?php echo $webPath ?>/img/logo.svg" alt="HeadReach" title="HeadReach">
					</a>
				</li><li>
					<a <?php echo ( $active_page == 'prospector' ? 'class="active"' : '' ); ?> href="<?php echo $webPath ?>/prospector" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Find people">
						<img src="<?php echo $webPath ?>/img/icn-search-white.svg" width="20" alt="Search">Search
					</a>
				</li><li>
					<a <?php echo ( $active_page == 'contacts' ? 'class="active"' : '' ); ?> href="<?php echo $webPath ?>/contacts" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Your contact book">
						<img src="<?php echo $webPath ?>/img/icn-person.svg" width="19" alt="Contacts">Contacts
					</a>
				</li><li>
					<a <?php echo ( $active_page == 'searches' ? 'class="active"' : '' ); ?> href="<?php echo $webPath ?>/searches" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Log with your previous searches">
						<img src="<?php echo $webPath ?>/img/icn-notepad.svg" width="19" alt="Contacts">Log
					</a>
				</li><li>
					<a href="mailto:contact@headreach.com" aria-haspopup="false" data-disable-hover="false" tabindex="1">
						<img src="<?php echo $webPath ?>/img/icn-social.svg" width="19" alt="Contacts">Contact
					</a>
				</li><?php if ( !$has_sub ) : ?><li><a href="<?php echo $websiteURL ?>/my-account/subscription/" class="upgrade-nav-button show-for-large" data-tooltip aria-haspopup="true" data-disable-hover="false" tabindex="1" title="Get more credits"><span>★</span> Upgrade now</a></li>
				<?php endif; ?>
			</ul>
		</div>
		<!-- Left Section End -->
		
		<!-- Right Section Start -->
		<div class="column shrink">
			<ul class="menu dropdown float-right header-nav" data-dropdown-menu>

				<?php if ( !$has_sub ) : ?>

					<!-- <li class="progress-wrapper" data-step="<?php echo $searches['step']; ?>">
						<a href="<?php echo $websiteURL ?>/my-account/subscription/" class="credits" data-tooltip  data-disable-hover="false" tabindex="1" title="<?php echo $searches['left'] ?> searches left. Upgrade your plan to get more searches">
							<div class="clearfix">
								<span class="float-left">Searches</span>
								<span class="float-right"><span id="credits-used" class="highlight"><?php echo $searches['used'] ?></span> used  <span class="divider">/</span> <span class="highlight"><?php echo $searches['total'] ?></span> total</span>
							</div>
							<div class="progress" role="progressbar" tabindex="0" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">
								<div class="progress-meter" style="width: <?php echo $searches['percentage_used'] ?>%"></div>
							</div>
						</a>
					</li> -->

				<?php endif; ?>

				<li class="progress-wrapper" data-step="<?php echo $credits['step']; ?>">
					<a href="<?php echo $websiteURL ?>/my-account/subscription/" class="credits" data-tooltip  data-disable-hover="false" tabindex="1" title="<?php echo $credits['left'] ?> credits left. Upgrade your plan to get more credits">
						<div class="clearfix">
							<span class="float-left">Credits</span>
							<span class="float-right"><span id="credits-used" class="highlight"><?php echo $credits['used'] ?></span> used  <span class="divider">/</span> <span class="highlight"><?php echo $credits['total'] ?></span> total</span>
						</div>
						<div class="progress" role="progressbar" tabindex="0" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">
							<div class="progress-meter" style="width: <?php echo $credits['percentage_used'] ?>%"></div>
						</div>
					</a>
				</li>

				<li class="support-nav-button">
					<a target="_blank" href="http://help.headreach.com/" data-tooltip  data-disable-hover="false" tabindex="1" title="Help"><span class="badge info-white"></span></a>
				</li>
			
				<li>
					<a>
						<span class="avatar" style="background-image: url(<?php echo $avatar; ?>)"></span>
						<span class="hide-for-medium-only"><?php echo $first_name; ?></span>
					</a>

					<ul class="menu">
						<li>
							<a href="<?php echo $webPath ?>/searches">Search Log</a>
						</li>
						<li>
							<a href="<?php echo $websiteURL ?>/my-account/subscription/">Subscription <span class="button orange hollow tiny subpixel upgrade-button-in-dropdown">Upgrade</span></a>
						</li>
						<li>
							<a href="<?php echo $websiteURL ?>/my-account/">Account Settings</a>
						</li>
						<li>
							<a href="<?php echo $websiteURL ?>/my-account/customer-logout/">Logout</a>
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
					<a href="<?php echo $webPath ?>/prospector" class="logo">
						<img src="<?php echo $webPath ?>/img/logo.svg" alt="HeadReach" title="HeadReach">
					</a>
				</li>
				<li>
					<a class="active" href="<?php echo $webPath ?>/prospector">
						<img src="<?php echo $webPath ?>/img/icn-search-white.svg" width="20" alt="Search">
					</a>
				</li>
				<li>
					<a href="<?php echo $webPath ?>/contacts">
						<img src="<?php echo $webPath ?>/img/icn-person.svg" width="19" alt="Contacts">
					</a>
				</li>
				<li>
					<a href="<?php echo $webPath ?>/searches">
						<img src="<?php echo $webPath ?>/img/icn-notepad.svg" width="19" alt="Contacts">
					</a>
				</li>
			</ul>
		</div>
		<!-- Left Section End -->
		
		<!-- Right Section Start -->
		<div class="column shrink">
			<ul class="menu dropdown header-nav" data-dropdown-menu>
			  	<li>
					<a><span class="avatar" style="background-image: url(<?php echo $avatar; ?>)"></span></a>
					<ul class="menu">
						<li>
							<a href="<?php echo $webPath ?>/searches">Search Log</a>
						</li>
						<li>
							<a href="<?php echo $websiteURL ?>/my-account/subscription/">Subscription <span class="button orange hollow tiny subpixel upgrade-button-in-dropdown">Upgrade</span></a>
						</li>
						<li>
							<a href="<?php echo $websiteURL ?>/my-account/">Account Settings</a>
						</li>
						<li>
							<a href="<?php echo $websiteURL ?>/my-account/customer-logout/">Logout</a>
						</li>
					</ul>
			  	</li>
		  	</ul>
		</div>
		<!-- Right Section End -->
	
	</div>
	
</header>
<!-- Top Nav for Small Screen End -->