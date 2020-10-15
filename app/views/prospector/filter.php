<?php
	use app\models\JobTaskMeta;

	$webPath = Yii::$app->params['webPath'];
	$search_type = isset( $main_job_data['search_type'] ) ? $main_job_data['search_type'] : '';
	$parameters = isset( $main_job_data['parameters'] ) ? $main_job_data['parameters'] : '';

	$parameters = unserialize( $parameters );

	if ( isset($tasks) AND $search_type == 3 ) {
		$tasksModel = new JobTaskMeta();
		$tasksModel->task_id = $tasks[0]['id'];

		$task_keyword = $tasksModel->getEntries( 'single' );
		if ( $task_keyword ) {
			$main_job_data['search_term'] = $task_keyword['keyword'];
		}
	}


	$current_only = false;
	if ( isset($parameters['current_only']) AND !empty($parameters['current_only']) ) {
		$current_only = true;
	}

	$start_date = '';
	if ( isset($parameters['start_date']) AND !empty($parameters['start_date']) ) {
		$start_date = $parameters['start_date'];
	}

	$active_company_tab = '';
	if ( isset($parameters['company']) OR $search_type == '2' OR $search_type == '3' ) {
		$active_company_tab = 'expanded';
	}

	$active_name_tab = '';
	if ( isset($parameters['name']) OR $search_type == '1' ) {
		$active_name_tab = 'expanded';
	}

	$current_page = Yii::$app->requestedRoute;
?>

<!-- Filter Section Start -->
<div class="filter-wrapper">
	<div class="filter-content <?php echo $active_company_tab; ?>" id="filter-2" data-param="company" data-toggler=".expanded">
		
		<div class="filter-content-wrapper">

			<?php if ( ($search_type == '2' OR $search_type == '3') AND $main_job_data['search_term'] ) : ?>

				<div class="filter-content-just-text"> 
					<?php echo $main_job_data['search_term']; ?> <span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="To search for a different company use the main search on top."></span>
				</div>

			<?php else : ?>
				
				<label class="text-input-label">
					<input data-toggle="enter-prompt" class="text-input autocomplete-field" data-format="html" data-entries-url="/app/web/prospector/getcompanies" placeholder="Add a company" type="text">
					<div class="dropdown-pane right dark press-enter hide-for-medium-only">
						<small class="subpixel">Press <span class="enter-button">Enter ⏎</span> to add</small>
					</div>
				</label>

				<?php 
					$args = array(
						'parameters' => $parameters,
						'key' => 'company',
					);
					echo Yii::$app->controller->renderPartial('/partials/tags-current', $args);
				?>

			<?php endif; ?>

		</div>
	</div>
	<a class="filter-toggle" data-toggle="filter-2"><img src="<?php echo $webPath ?>/img/icn-target.svg">Company</a>
</div>
<!-- Filter Section End -->

<!-- Filter Section Start -->
<div class="filter-wrapper">
	<div class="filter-content <?php echo $active_name_tab; ?>" id="filter-5" data-param="name" data-toggler=".expanded">
		<div class="filter-content-wrapper">

			<?php if ( $search_type == '1' AND $main_job_data['search_term'] ) : ?>

				<div class="filter-content-just-text"> 
					<?php echo $main_job_data['search_term']; ?> <span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="To search for a different name use the main search on top."></span>
				</div>

			<?php else : ?>
				
				<p>Type a first name and/or last name and press enter. You can search for multiple names at a time</p>

				<label class="text-input-label">
					<input type="text" placeholder="Add a person's name" class="text-input">
					<div class="dropdown-pane right dark press-enter hide-for-medium-only">
						<small class="subpixel">Press <span class="enter-button">Enter ⏎</span> to add</small>
					</div>
				</label>

				<?php 
					$args = array(
						'parameters' => $parameters,
						'key' => 'name',
					);
					echo Yii::$app->controller->renderPartial('/partials/tags-current', $args);
				?>

			<?php endif; ?>

		</div>
	</div>
	<a class="filter-toggle" data-toggle="filter-5"><img src="<?php echo $webPath ?>/img/icn-person-2.svg">Name</a>
</div>
<!-- Filter Section End -->

<!-- Filter Section Start -->
<div class="filter-wrapper">
	<div class="filter-content <?php echo ( isset($parameters['position']) ? 'expanded' : '' ); ?>" id="filter-4" data-param="position" data-toggler=".expanded">
		<div class="filter-content-wrapper">
			
			<label class="text-input-label">
				<input data-toggle="enter-prompt-3" data-format="position" data-entries-url="/app/web/prospector/getpositions" type="text" placeholder="Add a job position" class="text-input autocomplete-field">
				<div class="dropdown-pane right dark press-enter hide-for-medium-only">
					<small class="subpixel">Press <span class="enter-button">Enter ⏎</span> to add</small>
				</div>
			</label>

			<?php 
				$args = array(
					'parameters' => $parameters,
					'key' => 'position',
				);
				echo Yii::$app->controller->renderPartial('/partials/tags-current', $args);
			?>

		</div>
	</div>
	<a class="filter-toggle" data-toggle="filter-4"><img src="<?php echo $webPath ?>/img/icn-briefcase.svg">Position</a>
</div>
<!-- Filter Section End -->

<!-- Filter Section Start -->
<div class="filter-wrapper">
	<div class="filter-content <?php echo ( isset($parameters['industry']) ? 'expanded' : '' ); ?>" id="filter-3" data-param="industry" data-toggler=".expanded">
		
		<div class="filter-content-wrapper">		
			<label class="text-input-label">
				<input data-toggle="enter-prompt-4" data-format="plain" data-entries-url="/app/web/prospector/getindustries" type="text" placeholder="Add an industry" class="text-input autocomplete-field">
				<div class="dropdown-pane right dark press-enter hide-for-medium-only">
					<small class="subpixel">Press <span class="enter-button">Enter ⏎</span> to add</small>
				</div>
			</label>

			<?php 
				$args = array(
					'parameters' => $parameters,
					'key' => 'industry',
				);
				echo Yii::$app->controller->renderPartial('/partials/tags-current', $args);
			?>
		</div>

	</div>
	<a class="filter-toggle" data-toggle="filter-3"><img src="<?php echo $webPath ?>/img/icn-target-many.svg">Industry</a>
</div>
<!-- Filter Section End -->

<!-- Filter Section Start -->
<div class="filter-wrapper">
	<div class="filter-content <?php echo ( isset($parameters['keywords']) ? 'expanded' : '' ); ?>" id="filter-7" data-param="keywords" data-toggler=".expanded">
		
		<div class="filter-content-wrapper">
			<p>Type a relevant keyword like interest and press Enter. You can add multiple keywords.</p>

			<label class="text-input-label">
				<input data-toggle="enter-prompt-5" type="text" placeholder="Add a keyword" class="text-input">
				<div class="dropdown-pane right dark press-enter hide-for-medium-only">
					<small class="subpixel">Press <span class="enter-button">Enter ⏎</span> to add</small>
				</div>
			</label>

			<?php 
				$args = array(
					'parameters' => $parameters,
					'key' => 'keywords',
				);
				echo Yii::$app->controller->renderPartial('/partials/tags-current', $args);
			?>
		</div>

	</div>
	<a class="filter-toggle" data-toggle="filter-7"><img src="<?php echo $webPath ?>/img/icn-keywords.svg">Keywords</a>
</div>
<!-- Filter Section End -->		

<!-- Filter Section Start -->
<div class="filter-wrapper">
	<div class="filter-content <?php echo ( isset($parameters['country']) ? 'expanded' : '' ); ?>" id="filter-8" data-param="country" data-toggler=".expanded">
		
		<div class="filter-content-wrapper">
			<label class="text-input-label">
				<input data-toggle="enter-prompt-6" data-format="plain" data-entries-url="/app/web/prospector/getcountries" type="text" placeholder="Add a country" class="text-input autocomplete-field">
				<div class="dropdown-pane right dark press-enter hide-for-medium-only">
					<small class="subpixel">Press <span class="enter-button">Enter ⏎</span> to add</small>
				</div>
			</label>

			<?php 
				$args = array(
					'parameters' => $parameters,
					'key' => 'country',
				);
				echo Yii::$app->controller->renderPartial('/partials/tags-current', $args);
			?>			
		</div>

	</div>
	<a class="filter-toggle" data-toggle="filter-8"><img src="<?php echo $webPath ?>/img/icn-country.svg">Country</a>
</div>
<!-- Filter Section End -->

<!-- Filter Section Start -->
<div class="filter-wrapper">
	<div class="filter-content <?php echo ( isset($parameters['location']) ? 'expanded' : '' ); ?>" id="filter-9" data-param="location" data-toggler=".expanded">
		
		<div class="filter-content-wrapper">
			<p>Narrow your search to a city, town or area.</p>

			<label class="text-input-label">
				<input data-toggle="enter-prompt-7" data-format="plain" data-entries-url="/app/web/prospector/getlocations" type="text" placeholder="Add a location" class="text-input autocomplete-field">
				<div class="dropdown-pane right dark press-enter hide-for-medium-only">
					<small class="subpixel">Press <span class="enter-button">Enter ⏎</span> to add</small>
				</div>
			</label>

			<?php 
				$args = array(
					'parameters' => $parameters,
					'key' => 'location',
				);
				echo Yii::$app->controller->renderPartial('/partials/tags-current', $args);
			?>	
		</div>

	</div>
	<a class="filter-toggle" data-toggle="filter-9"><img src="<?php echo $webPath ?>/img/icn-pin.svg">Location</a>
</div>
<!-- Filter Section End -->	

<!-- Filter Section Start -->
<div class="filter-wrapper">
	<div class="filter-content <?php echo ( isset($parameters['start_date']) ? 'expanded' : '' ); ?>" id="filter-12" data-param="start_date" data-toggler=".expanded">
	
		<div class="filter-content-wrapper">
			<p>Use this filter in combination with the Position filter to narrow down a job start date. For ex., find all people that have started a project manager position on September 2016.</p>

			<label class="text-input-label">
				<input type="text" placeholder="Choose date" class="text-input date-picker" value="<?php echo ( isset($start_date[0]) ? $start_date[0] : '' ); ?>">
			</label>
		</div>

	</div>
	<a class="filter-toggle" data-toggle="filter-12"><img src="<?php echo $webPath ?>/img/icn-calendar.svg">Job start date</a>
</div>
<!-- Filter Section End -->

<!-- Filter Section Start -->
<div class="filter-wrapper">
	<div class="filter-content <?php echo ( isset($parameters['school']) ? 'expanded' : '' ); ?>" id="filter-10" data-param="school" data-toggler=".expanded">
		
		<div class="filter-content-wrapper">
			<p>Narrow your search to an university or school.</p>

			<label class="text-input-label">
				<input data-toggle="enter-prompt-8" type="text" placeholder="Add an university or school" class="text-input">
				<div class="dropdown-pane right dark press-enter hide-for-medium-only">
					<small class="subpixel">Press <span class="enter-button">Enter ⏎</span> to add</small>
				</div>
			</label>

			<?php 
				$args = array(
					'parameters' => $parameters,
					'key' => 'school',
				);
				echo Yii::$app->controller->renderPartial('/partials/tags-current', $args);
			?>
		</div>

	</div>
	<a class="filter-toggle" data-toggle="filter-10"><img src="<?php echo $webPath ?>/img/icn-university.svg">University or school</a>
</div>
<!-- Filter Section End -->

<!-- Filter Section Start -->
<div class="filter-wrapper degree-filter">
	<div class="filter-content <?php echo ( isset($parameters['degree']) ? 'expanded' : '' ); ?>" id="filter-11" data-param="degree" data-toggler=".expanded">
		
		<div class="filter-content-wrapper">
			
			<label class="text-input-label">
				<select>
					<option value="">Choose degree...</option>
					<option value="College degree">College degree</option>
					<option value="Bachelor degree">Bachelor's degree</option>
					<option value="Master degree">Master's degree</option>
					<option value="Doctor degree">Doctor's degree</option>
				</select>
			</label>
		
		</div>
	</div>
	<a class="filter-toggle" data-toggle="filter-11"><img src="<?php echo $webPath ?>/img/icn-degree.svg">Degree</a>
</div>
<!-- Filter Section End -->

<!-- Filter Section Start -->
		
<div class="filter-no-toggle"> 
	<label>
		<div class="row">
			<div class="column shrink">
				<input <?php echo ( $current_only ? 'checked' : '' ); ?> class="filter-current" type="checkbox" data-param="current_only" value="1" >
				<i></i>
			</div>
			<div class="column">
				Current positions only
			</div>
		</div>
	</label>
																
</div>

<div class="validation-message">
	<p>Please enter at least one search criteria</p>
</div>
	
<!-- Filter Section End -->

<?php if ( $current_page == 'prospector/advancedsearch' ) : ?>

	<div class="text-center adv-search">
		<a href="#" class="button orange small">Search</a>
	</div>

<?php else : ?>
	
	<div class="push-20 text-center adv-search">
		<a href="#" class="button hollow small">Search</a>
	</div>

<?php endif; ?>
							

<div class="parameters">

	<?php 
		$param_data = array(
			'company', 'name', 'position', 'industry', 'keywords', 'country', 'location', 'start_date', 'school', 'degree', 'current_only'
		);

		$output = array();

		if ( $search_type == 2 OR $search_type == 3 ) {
			$parameters['company'][] = $main_job_data['search_term'];
		} else if ( $search_type == 1 ) {
			$parameters['name'][] = $main_job_data['search_term'];
		}

		foreach ($param_data as $data) {

			if ( isset($parameters[$data]) AND !empty($parameters[$data]) ) {
				foreach ($parameters[$data] as $db_val) {
					$output[$data][] = $db_val;
				}
			} else {
				$output[$data] = array();
			}

		}

		$encoded_data = json_encode( $output );
		$encoded_data = str_replace( array('\n', '\t', '\r'), ' ', $encoded_data );
	?>

	<script type="text/javascript">
		var parameters = '<?php echo $encoded_data; ?>';
	</script>

</div>