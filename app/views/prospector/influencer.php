<?php
	use app\controllers\ProspectorController;
	use app\models\IDMasking;

	$webPath = Yii::$app->params['webPath'];
	$websiteURL = Yii::$app->params['websiteURL'];
	$socials_map = ProspectorController::getSocials();

	$photo = $influencer['photo_path'];

	if ( empty($photo) ) {
		$photo = $websiteURL . '/wp-content/themes/headreach/images/app/user-avatar.png';
	} else {
		// $photo = $websiteURL . '/app/assets/' . $photo;
	}

	$socials = '';
	if ( $influencer['person_social'] ) {
		$socials = json_decode( $influencer['person_social'] );
	}

	$id = IDMasking::maskID( $influencer['id'] );
	$name = $influencer['name'];
	$names_p = explode(' ', $name);
	$first_name = $names_p[0];

	$company_name = 'N/A';

	// echo '<pre>';
	// print_r( $influencer );
	// echo '</pre>';
	// exit;

	if ( $influencer['company'] ) {
		$company_name = $influencer['company'];
	}

	$emails = $influencer['emails_data'];

	$fullcontact_data = $influencer['full_contact_person_json'];
?>

<!-- Tab Navigation Start -->
<div class="tabs-wrapper clearfix">
	<ul class="tabs" data-tabs id="example-tabs">
		<li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Person</a></li>
		<li class="tabs-title"><a href="#panel2">Company</a></li>
	</ul>
	<button class="close-button" data-close aria-label="Close reveal" type="button">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
<!-- Tab Navigation End -->

<div class="popup-overlay">
	<div class="overlay-wrapper">
		<div class="loading-bar"></div>
		<div class="loading-bar"></div>
		<div class="loading-bar"></div>
		<div class="loading-bar"></div>
		<div class="loading-bar"></div>
	</div>
</div>

<div class="tabs-content" data-tabs-content="example-tabs">
	<!-- Person Tab Start -->
	<div class="tabs-panel is-active" id="panel1">
	  	<div class="avatar" style="background-image: url(<?php echo $photo; ?>)"></div>
	  	<span class="name text-center"><?php echo $name; ?></span>	
	  	<span class="light text-center"><?php echo strip_tags( $influencer['title'] ); ?></span>

	  	<?php if ( !$is_searched AND true ) : ?>
	  		
	  		<div style="display: none;" class="text-center find-socials">
				<a class="button small hollow" href="#" data-influencer-id="<?php echo $id; ?>" data-name="<?php echo $name; ?>">Find <?php echo $first_name; ?>'s email and social profiles</a>
				<div class="loading loading-socials">
					<div class="loading-bars-wrapper">
						<div class="loading-bar"></div>
						<div class="loading-bar"></div>
						<div class="loading-bar"></div>
						<div class="loading-bar"></div>
						<div class="loading-bar"></div>
					</div>
					<div class="text">Finding <?php echo $first_name; ?>'s email and social profiles...</div>
				</div>
			</div>

	  	<?php endif; ?>
	  	
	  	<?php if ( $is_searched AND $emails AND !true ) : ?>

		  	<div class="text-center subpixel push-20">
		  		<a href="mailto:<?php echo $emails[0]['email']; ?>" class="button text-center">
		  			<img width="17" class="envelope" src="<?php echo $webPath; ?>/img/icn-envelope.svg">Email <?php echo $first_name; ?>
		  		</a>
		  	</div>

		<?php endif; ?>
	  		
  		<!-- User Details Start -->
  		<table class="profile-details">

  			<?php if ( $influencer['description'] ) : ?>

				<tr>
					<td>Description</td>
					<td><?php echo $influencer['description']; ?></td>
				</tr>

			<?php endif; ?>

			<?php if ( $influencer['location'] ) : ?>

				<tr>
					<td>Location</td>
					<td><?php echo $influencer['location']; ?></td>
				</tr>

			<?php endif; ?>

	  		<?php if ( $is_searched AND $emails ) : ?>

		  		<tr>
			  		<td>Email</td>

			  		<td>

			  		<?php
						$data = [
							'emails' => $emails,
							'first_name' => $first_name,
							'person_data' => $influencer,
							'in_popup' => true,
						];
						echo Yii::$app->controller->renderPartial('/partials/list-emails', $data);
					?>

			     	</td>
		  		</tr>

		  	<?php endif; ?>

	  		<?php if ( $influencer['bio'] ) : ?>

				<tr>
					<td>Bio</td>
					<td><?php echo nl2br( strip_tags($influencer['bio']) ); ?></td>
				</tr>

		  	<?php endif; ?>
	  		
			<?php if ( $is_searched AND $socials ) : ?>

		  		<tr>
			  		<td>Social</td>
			  		<td class="social">
						<div class="clearfix">

							<ul class="social-profiles-menu">

								<?php foreach ($socials as $social): 
									preg_match( '~^(.*\/\/[^\/?#]*).*$~', $social, $domain );
									$root_domain = preg_replace('~^https?://~', '', $domain[1]);

									$icon = 'other';

									foreach ($socials_map as $pd_icon => $value) {
										if ( preg_match('~'. $value .'~', $root_domain) ) {
											$icon = $pd_icon;
										}
									}
								?>
									
									<li>
										<a target="_blank" href="<?php echo $social; ?>">
											<img src="https://www.fullcontact.com/wp-content/themes/fullcontact/assets/images/social/<?php echo $icon; ?>.png">
										</a>
									</li>

								<?php endforeach ?>

							</ul>
						</div>
					</td>	
		  		</tr>

	  		<?php endif; ?>

	  		<?php
				$fdata = [
					'fullcontact_data' => $fullcontact_data
				];
				echo Yii::$app->controller->renderPartial('/partials/fullcontact-data', $fdata);
			?>
	  		
		</table>
  		<!-- User Details End -->
	  		
	</div>
	<!-- Person Tab End -->


	<?php
		$show_extra_info = false;
		if ( $company_data ) {
			$company_name = $company_data->name;
			$company_website = $company_data->website;
			$show_extra_info = true;
			$socials = $company_data->social_profiles;
			$emails = $company_data->email_addresses;
			$phones = $company_data->tel_numbers;
			$full_data = json_decode( $company_data->json_response );

			$linked_desc = false;

			$social_profiles_full = $full_data->socialProfiles;

			if ( $social_profiles_full ) {
				foreach ($social_profiles_full as $spf) {
					if ( $spf->typeId == 'linkedincompany' AND isset($spf->bio) ) {
						$linked_desc = $spf->bio;
					}
				}
			}

			$founded = '';
			$approxEmployees = '';
			$addresses = array();

			$organization = $full_data->organization;

			if ( isset($organization->founded ) ) {
				$founded = $organization->founded;
			}

			if ( isset($organization->approxEmployees) ) {
				$approxEmployees = $organization->approxEmployees;
			}

			if ( isset($organization->contactInfo->addresses) ) {
				foreach ($organization->contactInfo->addresses as $address) {
					$tmp_addrr = '';
					if ( isset($address->locality) ) {
						$tmp_addrr = $address->locality . ' ';
					}

					if ( isset($address->region->name) ) {
						$tmp_addrr .= $address->region->name . ', ';
					}

					if ( isset($address->country->code) ) {
						$tmp_addrr .= $address->country->code;
					}

					if ( $tmp_addrr ) {
						$addresses[] = $tmp_addrr;
					}
				}
			}

		}

	?>
	  
	<!-- Company Tab Start -->
	<div class="tabs-panel" id="panel2">
		<?php if ( isset($full_data->logo) ) : ?>
		  	
		  	<div class="avatar company" style="background-image: url(<?php echo $full_data->logo; ?>)"></div>

		<?php else : ?>

		  	<div class="avatar company"></div>

		<?php endif; ?>

	  	<span class="name text-center"><?php echo $company_name; ?></span>

	  	<?php if ( $show_extra_info ) : ?>

		  	<?php if ( $addresses ) :
		  	?>

			  	<span class="light text-center"><?php echo implode('<br />', $addresses); ?></span>

			  <?php endif; ?>

			<?php if ( $emails ) : ?>
		  	
			  	<div class="subpixel text-center push-20">
			  		<a href="mailto:<?php echo $emails[0] ?>" class="button text-center"><img width="17" class="envelope" src="<?php echo $webPath; ?>/img/icn-envelope.svg">Email <?php echo $company_name; ?></a>	
			  	</div>

			  <?php endif; ?>
		  	
	  		<!-- Company Details Start -->
	  		<table class="profile-details">
		  			
		  		<?php if ( $emails ) : ?>

			  		<tr>
				  		<td>Email</td>
				  		<td>
				  			<?php foreach ($emails as $email): ?>
				  				<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br />
				  			<?php endforeach ?>
				  		</td>
			  		</tr>

		  		<?php endif; ?>

		  		<?php if ( $company_website ) : ?>

			  		<tr>
				  		<td>Website</td>
				  		<td>
				  			<a target="_blank" href="<?php echo $company_website; ?>">
				  				<?php echo $company_website; ?>
				  			</a>
				  		</td>
			  		</tr>

		  		<?php endif; ?>

		  		<?php if ( $linked_desc ) : ?>
		  			
					<tr>
						<td>Description</td>
						<td><?php echo nl2br( strip_tags($linked_desc) ); ?></td>
					</tr>

		  		<?php endif; ?>

		  		<?php if ( isset($organization->links) AND !empty($organization->links) ) : ?>

		  			<tr>
				  		<td>Links</td>
				  		<td>
				  			<?php foreach ($organization->links as $i => $link): ?>
					  			
					  			<a target="_blank" href="<?php echo $link->url; ?>">
					  				<?php echo ucfirst($link->label); ?>
					  			</a>

					  			<?php if ( ($i+1) < count($organization->links) ) echo '<br />'; ?>
				  				
				  			<?php endforeach ?>

				  		</td>
			  		</tr>

		  		<?php endif; ?>

		  		<?php if ( $phones ) : ?>

			  		<tr>
				  		<td>Phone</td>
				  		<td>
				  			<?php 
				  				foreach ($phones as $phone) {
				  					echo $phone . '<br />';
				  				}
				  			?>
				  		</td>
			  		</tr>

			  	<?php endif; ?>

			  	<?php if ( $founded ) : ?>
			  		<tr>
				  		<td>Founded</td>
				  		<td><?php echo $founded; ?></td>
			  		</tr>
			  	<?php endif; ?>

			  	<?php if ( $approxEmployees ) : ?>
			  		<tr>
				  		<td>Employees</td>
				  		<td><?php echo $approxEmployees; ?></td>
			  		</tr>
			  	<?php endif; ?>
		  		
		  		<tr>
			  		<td>Social</td>
			  		<td class="social">
						<div class="clearfix">
							<ul class="social-profiles-menu">

								<?php foreach ($socials as $social): 
									preg_match( '~^(.*\/\/[^\/?#]*).*$~', $social, $domain );
									$root_domain = preg_replace('~^https?://~', '', $domain[1]);

									$icon = array_search( $root_domain, $socials_map );

									if ( empty($icon) ) {
										$icon = 'other';
									}
								?>
									
									<li>
										<a target="_blank" href="<?php echo $social; ?>">
											<img src="https://www.fullcontact.com/wp-content/themes/fullcontact/assets/images/social/<?php echo $icon; ?>.png">
										</a>
									</li>

								<?php endforeach ?>

							</ul>
						</div>
					</td>
		  		</tr>

		  		<?php if ( isset($organization->keywords) AND !empty($organization->keywords) ) : ?>

					<tr>
						<td>Keywords/Topics</td>
						<td>
							<?php
								foreach ($organization->keywords as $j => $keyword) {
									echo $keyword . ( ($j+1) < count($organization->keywords) ? ', ' : '' );
								}
							?>
						</td>
					</tr>

				<?php endif; ?>
		  		
			</table>
	  		<!-- Company Details End -->

	  	<?php endif; ?>
	  		
	</div>
	<!-- Company Tab End -->
</div>