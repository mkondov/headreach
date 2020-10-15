<?php 
	$socials = array(
		'Facebook' => 'facebook.png',
		'Twitter' => 'twitter.png',
		'Pinterest' => 'pinterest.png',
		'LinkedIn' => 'linkedin.png',
		'About Me' => 'aboutme.png',
		'Klout' => 'klout.png',
		'YouTube' => 'youtube.png',
		'Quora' => 'quora.png',
		'Skype' => 'skype.png',
	);
?>

<div class="right-sidebar shrink columns show-for-large">
  	
  	<div data-sticky-container>

  		<div data-sticky data-anchor="content" data-options="marginTop:0;">
			<div class="right-sidebar-header text-center">
  				<p href="#" title="Person info" class="arrow-left"></p>
  				Person info
  				<p href="#" title="Company info" class="arrow-right"></p>
	  		</div>
  			
  			<ul class="slide-content">
  				<li class="slide-entry">
					<div class="text-center right-sidebar-name">
					</div>

					<dl class="right-sidebar-details push-top-20">	  	
						<dt>Bio <a data-toggle="about-hidden">View</a></dt>
						<dd class="sidebar-bio about"></dd>

						<dt>Company</dt>
						<dd class="sidebar-company"></dd>

						<dt>Position</dt>
						<dd class="sidebar-position"></dd>

						<dt>Email</dt>
						<dd class="sidebar-email"></dd>

						<dt class="hide">Social Media</dt>
						<dd class="clearfix" style="display: none;">
							<ul class="social-profiles-menu">

								<?php foreach ($socials as $class => $image): ?>
									
									<li>
										<a class="$class" href="#">
											<img alt="<?php echo $class; ?>" src="https://www.fullcontact.com/wp-content/themes/fullcontact/assets/images/social/<?php echo $image; ?>">
										</a>
									</li>

								<?php endforeach ?>

							</ul>
						</dd>
					</dl>

					<div class="social-data">
						<p>Personal Socials</p>
						<div class="sidebar-personal-socials">
						</div>
						<p>Company Socials</p>
						<div class="sidebar-company-socials">
						</div>
					</div>
				</li>
  			</ul>

			<!-- Sniper Search Start -->
			<div class="sniper-search push-top-30">
				Looking for someone else?

				<div class="input-group">
					<label>
						Search <strong>How Stuff Works</strong> for:
						<input class="input-group-field left" type="text" placeholder="Job position" data-tooltip title="For example: 'editor'. This will look for all editors in this company.">
						<div class="input-group-button">
							<input data-open="sniperSearchModal" type="submit" class="search-icon" value="Search" title="Search for person">
						</div>

					</label>
				</div>
			</div>
			<!-- Sniper Search End -->
  		</div>

  	</div>

</div>