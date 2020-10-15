<?php
	$credits = getCredits();
	$home = get_option( 'home' );

	$has_sub = false;
	$label = 'You\'re on free trial';

	if ( function_exists('wcs_user_has_subscription') ) {
		$has_sub = wcs_user_has_subscription( '', '', 'active' );
	}

	if ( $has_sub ) {
		$label = 'Your subscription is active!';
	}
?>

<h2>Subscription</h2>

<!-- Credits left Start -->
<div class="block blue">
	<div class="float-left"><?php echo $label; ?></div>
	<div class="float-right text-right credits-left"><?php echo $credits['left'] ?> credits left</div>
	<div class="clearfix"></div>

	<div class="progress blue" role="progressbar" tabindex="0" aria-valuenow="<?php echo $credits['percentage_used'] ?>" aria-valuemin="0" aria-valuemax="100">
 		<div class="progress-meter" style="width: <?php echo $credits['percentage_used'] ?>%"></div>
	</div>	
</div>
<!-- Credits left End -->


<!-- Subscription block start for small -->
<div class="row">
	
	<div class="small-12 large-6 column">
		<div class="subscription">
				
			<div class="subscription-price">
				<div class="price-wrapper">
					<sup>$</sup>
					19
					<small>month</small>
				</div>
			</div>
			
			 <ul>
                <li>100 Credits <span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="We take away 1 credit out of your monthly allocation each time we successfully find an email address."></span></li>
                <!-- <li>200 Search Requests <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="A search request is counted every time you do a search through our People Finder or click on Search More People. A search request returns up to 10 people. For example, if we find 100 people for HubSpot you must use 10 search requets to view all of the people."></span></li> -->
                <li>Search from 400+ million business contacts</li>
                <li>Advanced search</li>
                <li>Search by person’s name</li>
                <li>Search by domain name</li>
                <li>Search by company</li>
                <li>Personal social profiles</li>
                <li>Relevant contextual data <span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="Person's biography, social followers, work history and interests."></span></li>
                <li>Company emails and social profiles</li>
                <li>Unlimited CSV Exports</li>
                <li>Email accuracy rates</li>
                <li>Up-to-date data updated daily</li>
            </ul>

			<div class="text-center">
				<a href="<?php echo $home ?>?add-to-cart=55" class="orange button push-20">Choose plan</a>
			</div>
		</div>
	</div>
	
	
	<div class="small-12 large-6 column">
		<div class="subscription">	
			<div class="subscription-price">
				<div class="price-wrapper">
					<sup>$</sup>
					39
					<small>month</small>
				</div>
			</div>
			
			<ul>
				<li>250 Credits <span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="We take away 1 credit out of your monthly allocation each time we successfully find an email address."></span></li>
            	<!-- <li>500 Search Requests <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="A search request is counted every time you do a search through our People Finder or click on Search More People. A search request returns up to 10 people. For example, if we find 100 people for HubSpot you must use 10 search requets to view all of the people."></span></li> -->
                <li>Everything from the small plan included</li>
                <!-- <li>Concierge email finding <span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="Not finding an email? We're going to find it for you. We're really good at finding emails. Just drop us an in-app message."></span></li> -->
            </ul>

			<div class="text-center">
				<a href="<?php echo $home ?>?add-to-cart=54" class="orange button push-20">Choose plan</a>
			</div>
		</div>
		
		
		<div class="subscription">	
			<div class="subscription-price">
				<div class="price-wrapper">
					<sup>$</sup>
					79
					<small>month</small>
				</div>
			</div>
			
			 <ul>
                <li>500 Credits <span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="We take away 1 credit out of your monthly allocation each time we successfully find an email address."></span></li>
                <!-- <li>1000 Search Requests <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="A search request is counted every time you do a search through our People Finder or click on Search More People. A search request returns up to 10 people. For example, if we find 100 people for HubSpot you must use 10 search requets to view all of the people."></span></li>-->
                <li>Everything from the small and medium plans included</li>
            </ul>
			<div class="text-center">
				<a href="<?php echo $home ?>?add-to-cart=26" class="orange button push-20">Choose plan</a>
			</div>
		</div>
	</div>

</div>
<!-- Subscription block end for small -->