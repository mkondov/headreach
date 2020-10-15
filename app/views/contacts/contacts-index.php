<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\controllers\ProspectorController;
use app\models\IDMasking;
use app\common\components\Helpers;
use app\common\components\Paging;

$this->title = 'Contacts - HeadReach';

$webPath = Yii::$app->params['webPath'];
$websiteURL = Yii::$app->params['websiteURL'];
$socials_map = ProspectorController::getSocials();

$session_data = Helpers::getSessionData();
$wp_user_id = $session_data['current-user']['ID'];

?>

<?php echo Yii::$app->controller->renderPartial('/layouts/header'); ?>

<!-- Mashead Start -->
<div class="masthead">
	<div class="row">
		<div class="small-12 column">
			<h4 class="float-left no-bottom-margin"><span class="module-title">Contacts</span> <span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="A list of all people, which you've looked for."></span></h4>
			
			<div class="float-right">
				<a data-toggle="export-dropdown" class="button white hollow small float-right action-button">Export</a>		
				<div class="dropdown-pane dropdown-pane-with-menu bottom right" id="export-dropdown" data-dropdown data-hover="true" data-hover-pane="true">
					<ul>
						<li>
							<a href="<?php echo $webPath ?>/contacts/export/all">Export all</a>
						</li>
						<li>
							<a href="<?php echo $webPath ?>/contacts/export">Export results with emails only</a>
						</li>
					</ul>
				</div>
			</div>			
		</div>
	</div>
</div>
<!-- Masthead End -->
		
<!-- Main Start -->
<div class="row" data-equalizer data-equalize-on="large">
	<div class="small-12 column">

		<?php if ( empty($contacts) ) : ?>

			<div class="row align-center account-panel">
				<div class="small-11 large-9 column">
					
					<div class="row small-collapse block">
						<div class="column">
							<div class="account-panel-content">
								<p>You don't have any contacts in your contact book.</p>						
								<a href="<?php echo $webPath ?>/searches" class="button hollow push-top-80">Add contacts</a>
							</div>
						</div>
					</div>

				</div>
			</div>

		<?php else : ?>
		
			<!-- Search Results Table Start -->
			<table class="tableWithFloatingHeader push-20">
				<thead>
					<tr>
						<th width="25%" colspan="2" class="results">Name and company</th>
						<th width="16.75%">Position</th>
						<th width="17.75%">Location</th>
						<th width="17.75%">Email</th>
						<th width="22.75%">Social Profiles</th>
						<th>Added</th>
					</tr>
				</thead>
				<tbody>

				<?php 
					if ( isset($_GET['debug']) ) {
						echo '<pre>';
						print_r( $contacts );
						echo '</pre>';
						exit;
					}
				?>

				<?php foreach ($contacts as $influencer):
					$photo = $influencer['photo_path'];

					if ( empty($photo) ) {
						$photo = $websiteURL . '/wp-content/themes/headreach/images/app/user-avatar.png';
					} else {
						// $photo = 'http://headreach.info/app/assets/' . $photo;
					}

					$socials = '';
					if ( $influencer['person_social'] ) {
						$socials = json_decode( $influencer['person_social'] );
					}

					$id = IDMasking::maskID( $influencer['id'] );

					$name = $influencer['name'];
					$name_pieces = explode(' ', $name);
					$first_name = $name_pieces[0];
				?>
					
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
							$emails = $influencer['emails_data'];
						?>

						<td data-th="Email" <?php echo ( $emails ? '' : 'class="text-center find-socials re-run"' ) ?>>
							<?php
								if ( $emails ) {
									$data = [
										'emails' => $emails,
										'first_name' => $first_name,
										'person_data' => $influencer,
									];
									echo Yii::$app->controller->renderPartial('/partials/list-emails', $data);
								} else {
									?>

									<a <?php echo ( $wp_user_id == 1469 ? '' : 'style="display: none;"' ); ?> href="#" data-influencer-id="<?php echo $id; ?>" data-name="<?php echo $name; ?>" class="button small hollow">Re-run search</a>

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

									<?php
								}
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

						<td style="font-size: 15px;">
							<?php 
								$added = strtotime( $influencer['timestamp_searched'] );
								echo date( 'M j, Y', $added );
							?>
						</td>

					</tr>
					
				<?php endforeach ?>

				</tbody>
			</table>

		<?php endif; ?>

		<?php
			$paging = new Paging();
			$paging->displayPaging( $count );
		?>
		
		<!-- Full Profile View Start -->
		<div class="reveal half" id="influencer-modal" data-reveal data-close-on-click="true" data-overlay="false" data-animation-in="slide-in-right fast">			
		</div>
		<!-- Full Profile View End -->	

	</div>	
</div>	
<!-- Main End -->