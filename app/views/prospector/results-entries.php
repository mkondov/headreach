<?php

use app\controllers\ProspectorController;
use app\models\IDMasking;
use app\common\components\Helpers;

$credits = Helpers::getCredits();
$websiteURL = Yii::$app->params['websiteURL'];
$socials_map = ProspectorController::getSocials();

foreach ($influencers as $influencer):
	$photo = $influencer['photo_path'];

	if ( empty($photo) ) {
		$photo = $websiteURL . '/wp-content/themes/headreach/images/app/user-avatar.png';
	}

	$id = IDMasking::maskID( $influencer['id'] );

	$socials = '';
	if ( $influencer['person_social'] ) {
		$socials = json_decode( $influencer['person_social'] );
	}

	$name = $influencer['name'];
	$name_pieces = explode(' ', $name);
	$first_name = $name_pieces[0];
?>
	
	<table class="table-load-more">
		<tbody class="tmp-body">
			
			<tr>
				<td data-th="">
					<div class="avatar-group">
						<a class="name-link" data-id="<?php echo $id; ?>">
							<!-- <div class="avatar company" style="background-image: url(https://stripe.com/img/event/fbStripeLogo.png)"></div> -->
							<div class="avatar" style="background-image: url(<?php echo $photo; ?>)"></div>
						</a>
					</div>
				</td>
				<td data-th="">
					<a class="name-link" data-id="<?php echo $id; ?>">
						<span class="name top" data-toggle="exampleModal8" data-tooltip  data-disable-hover="false" tabindex="1"
							title="View more info about <?php echo $name; ?><?php echo ( $influencer['company'] ? ' and ' . $influencer['company'] : '' ); ?>">
								<?php echo $name; ?>
						</span>

						<?php if ( isset($influencer['description']) AND !empty($influencer['description']) ) : ?>
							<span class="badge info-dark name-description" data-tooltip  data-disable-hover="false" tabindex="1" title="<?php echo $influencer['description']; ?>"></span>
						<?php endif; ?>

						<span class="light"><?php echo $influencer['company']; ?></span>
					</a>
				</td>
			    <td data-th="Position"><?php echo strip_tags( $influencer['title'] ); ?></td>
			    
				<td data-th="Location">		
					<?php echo $influencer['location']; ?>
				</td>

				<?php
					$is_searched = $influencer['is_searched'];
					$results_found = $influencer['results_found'];							

					// Current user already search for socials
					if ( $is_searched ) :
						$emails = $influencer['emails_data'];
				?>

					<td data-th="Email">
						<?php 
							$data = [
								'emails' => $emails,
								'first_name' => $first_name,
								'person_data' => $influencer,
							];
							echo Yii::$app->controller->renderPartial('/partials/list-emails', $data);
						?>
					</td>
					
					<td class="social">
						<div class="clearfix">

							<?php if ( $socials ) : ?>

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

							<?php endif; ?>

						</div>
					</td>

				<?php else : ?>

				    <td data-th="Email" colspan="2" class="text-center <?php echo ( $credits['left'] > 0 ? 'find-socials' : '' ); ?>">

				    	<?php if ( $credits['left'] > 0 ) : ?>

							<a href="#" data-influencer-id="<?php echo $id; ?>" data-name="<?php echo $name; ?>" class="button small hollow">Find <?php echo $first_name; ?>'s email and social profiles</a>

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

				    	<?php else : ?>

				    		<a rel="nofollow" class="button orange small hollow" href="<?php echo $websiteURL ?>/my-account/subscription/">Upgrade to find <?php echo $first_name; ?>'s email and social profiles</a>

				    	<?php endif; ?>

					</td>

				<?php endif; ?>

			</tr>

		</tbody>
	</table>
	
<?php endforeach ?>

<?php if ( $last_job ) : ?>

	<div class="navigation-buttons">
		<div class="nav-contents">
			<?php if ( empty($has_sub) AND $total_queries > 3 ) : ?>

				<div class="load-more-button">
					<a class="button orange" href="<?php echo $websiteURL ?>/my-account/subscription/">★ To load up to 1,000 people upgrade</a>
				</div>

			<?php elseif ( $last_job->has_next_page ) : ?>
			
				<a class="button load-button" href="#" data-query-id="<?php echo IDMasking::maskID( $last_job->id ); ?>">Load more results</a>

			<?php else : ?>

				<span class="button disabled">No more results</span>

			<?php endif; ?>
		</div>
	</div>

<?php endif; ?>