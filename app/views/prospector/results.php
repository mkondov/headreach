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

$this->title = 'People Finder - Results - HeadReach';

$webPath = Yii::$app->params['webPath'];
$websiteURL = Yii::$app->params['websiteURL'];
$socials_map = ProspectorController::getSocials();
$active_page = Yii::$app->controller->id;

$credits = Helpers::getCredits();

$search_term = $main_job_data->search_term;
$search_type = $main_job_data->search_type;

switch ($search_type) {
	case 1:
		$search_type_name = 'namesearch';
		$label = 'Name';
		$desc = 'Find anybody\'s email and social profiles by searching for their personal name.';
		break;
	case 2:
		$search_type_name = 'companysearch';
		$label = 'Company name';
		$desc = 'Find all people that work at a company, their verified emails and social profiles. For example, try typing HubSpot';
		break;
	case 3:
		$search_type_name = 'websitesearch';
		$label = 'Domain name';
		$desc = 'Find all people that are related to a domain name. For example enter “hubspot.com”.';
		break;
	case 4:
		$search_type_name = 'postscan';
		$label = 'Post URL';
		$desc = 'Scans your post for personal names, brand mentions and links and returns all relevant people and contact information.';
		break;
	case 5:
		$search_type_name = 'advancedsearch';
		$label = 'Advanced search';
		$desc = '';
		break;
}

$display_count = $count;
if ( $main_job_data['total_results'] AND $main_job_data['total_results'] > 10 ) {
	$display_count = $main_job_data['total_results'];
}

?>

<?php
	echo Yii::$app->controller->renderPartial('/layouts/header');

	$data = [
		'active_tab' => $search_type_name
	];
	echo Yii::$app->controller->renderPartial('/prospector/sub-navigation', $data);
?>

<!-- Mashead Start -->
<div class="masthead">
	<div class="row">
		<div class="small-12 column">

			<?php if ( $search_type == 5 ) : ?>

				<h4 class="no-bottom-margin">
					<span class="module-title">Advanced search</span>
					<span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="Find people using advanced search parameters."></span>
				</h4>

			<?php else : ?>

				<h4>
					<a href="<?php echo $webPath ?>/prospector">Search</a>:
					<span class="module-title">By <?php echo $label; ?></span>
					<span class="badge info-white" data-tooltip  data-disable-hover="false" tabindex="1" title="<?php echo $desc; ?>"></span>
				</h4>
				
				<!-- Module Search Start -->
				<form class="search-form" method="POST" action="/app/web/task/executequery" data-search_type="<?php echo $search_type; ?>">
					<div class="input-group module-search">

						<?php if ( $search_type == 2 ) : ?>

							<input data-format="html" data-entries-url="/app/web/prospector/getcompanies" name="search-form-field" class="input-group-field autocomplete-field" value="<?php echo ( $search_term ? $search_term : '' ); ?>" placeholder="<?php echo $label; ?>" type="search">

						<?php elseif ( $search_type == 3 ) : ?>

							<input data-format="html" data-entries-url="/app/web/prospector/getdomains" name="search-form-field" class="input-group-field autocomplete-field" value="<?php echo ( $search_term ? $search_term : '' ); ?>" placeholder="<?php echo $label; ?>" type="search">

						<?php else : ?>

							<input name="search-form-field" class="input-group-field" value="<?php echo ( $search_term ? $search_term : '' ); ?>" placeholder="<?php echo $label; ?>" type="search">

						<?php endif; ?>

						<a class="input-group-button button orange subpixel">Search</a>
					</div>
				</form>
				<!-- Module Search End -->

				<div class="success-progress-bar in-results">
					<h4>Generating your report. Please wait ..</h4>
				</div>

			<?php endif; ?>
				
		</div>
	</div>
</div>
<!-- Masthead End -->

<?php if ( empty($search_term) ) : ?>

	<!-- Main Start -->
	<div class="row">
		<div class="small-12 column">
			
			<div class="row align-justify push-10">
				<div class="column">
					<h4 class="results-number">The query you are looking for doesn't exist anymore.</h4>
				</div>
			</div>

		</div>
	</div>

<?php else : ?>
		
	<!-- Main Start -->
	<div class="row">
		
		<div class="small-12 column">

			<div class="row small-collapse">

				<!-- Filters Start -->
				<div class="show-for-large shrink column filters-column">
					<div class="filters">

						<?php if ( $search_type != 5 ) : ?>
							<div class="filters-head">Advanced search <!-- <small><a class="float-right" href="#">Reset</a></small> --></div>
						<?php else : ?>
							<br />
						<?php endif; ?>

						<?php
							$args = array(
								'main_job_data' => $main_job_data,
								'tasks' => $tasks,
							);
							echo Yii::$app->controller->renderPartial('/prospector/filter', $args);
						?>
					</div>
				</div>
				<!-- Filters End -->

				<!-- Search Results Start -->
				<div class="column search-results-column">

					<div class="row align-justify push-10">
						<div class="column">

							<?php if ( $search_type == 5 AND $count < 1 ) : ?>
								<h4 class="results-number"><b>No results</b> found for</h4>
							<?php else : ?>
								
								<h4 class="results-number">
									<strong><?php echo number_format( $display_count ); ?></strong>
									
									<?php echo ( $display_count > 1 ? 'People' : 'Person' ) ?>

									found
									<?php if ( $search_type != 5 ) : ?>
										for <em>“<?php echo $search_term; ?>”</em>
									<?php endif; ?>

									<?php if ( $display_count > 10 ) : ?>
										
										<span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="We can show up to 1,000 results per search. Total number of results is approximate."></span>

									<?php endif; ?>
								</h4>

							<?php endif; ?>
						</div>
						
						<?php if ( $display_count > 0 ) : ?>
							
						<div class="column shrink show-for-medium">
							<a href="#" disabled class="button hollow small" data-tooltip aria-haspopup="true" title="Find verified emails and social profiles of all people on this page">Find all emails</a>&nbsp;
							<a href="<?php echo $webPath . '/prospector/export/id/' . IDMasking::maskID( $main_job_data['id'] ) ?>" class="button hollow small" data-tooltip aria-haspopup="true" title="Export all results for this search as a CSV document">Export</a>
						</div>

						<?php endif; ?>
					</div>

					<?php
						$parameters = $main_job_data['parameters'];
						if ( $parameters ) :
					?>

						<div class="row">
							<div class="filters full-width xpush-20">
								<?php echo Yii::$app->controller->renderPartial('/partials/tags', array( 'parameters' => $parameters )); ?>
							</div>
						</div>

					<?php endif; ?>
					
					<!-- Search Results Table Start -->
					<table class="tableWithFloatingHeader push-20 table-listing">
						<thead>
							<tr>
								<th width="25%" colspan="2" class="results">Name and company</th>
								<th width="16.75%">Position</th>
								<th width="17.75%">Location</th>
								<th width="17.75%">Email</th>
								<th width="22.75%">Social Profiles</th>
							</tr>
						</thead>
						<tbody class="main-tbody">

							<?php foreach ($influencers as $influencer):
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
								
							<?php endforeach ?>
					
						</tbody>
					</table>			  	
					<!-- Search Results Table End -->

					<?php
						$paging = new Paging();
						$paging->displayPaging( $count );
					?>

					<?php
						if ( $queries ) :
							$total_queries = count( $queries );
							$max_page = ceil( $count / 20 );

							$last_query = end( $queries );
							$last_query_id = $last_query->id;
							
							if (
								$last_query->has_next_page AND
								( (isset($_GET['page']) AND $_GET['page'] == $max_page) OR $max_page == 1 )
							) :
					?>

							<?php if ( !$has_sub AND $total_queries > 3 ) : ?>

								<div class="load-more-button">
									<a class="button orange" href="<?php echo $websiteURL ?>/my-account/subscription/">★ To load up to 1,000 people upgrade</a>
								</div>

							<?php else : ?>

								<div class="load-more-button">
									<a class="button load-button" href="#" data-query-id="<?php echo IDMasking::maskID( $last_query_id ); ?>">Load more results</a>
								</div>

							<?php endif; ?>

						<?php endif; ?>
					<?php endif; ?>

				</div>
				<!-- Search Results End -->
				
				<!-- Full Profile View Start -->
				<div class="reveal half" id="influencer-modal" data-reveal data-close-on-click="true" data-overlay="false" data-animation-in="slide-in-right fast">
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
				</div>
				<!-- Full Profile View End -->

			</div>

		</div>
		
	</div>	
	<!-- Main End -->

<?php endif; ?>

<?php
	if ( $credits['left'] < 4 AND !$has_sub AND !isset($_COOKIE['hr-user-closed-credits']) ) {
		$data_credits = [
			'credits' => $credits,
			'websiteURL' => $websiteURL,
		];
		echo Yii::$app->controller->renderPartial('/partials/upgrade-slider', $data_credits);
	}
?>