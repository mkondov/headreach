<?php

function crb_get_query_data( $arg ) {
	
	if ( !isset($_GET[$arg]) ) {
		return array();
	}

	$queried_data = $_GET[$arg];

	if ( empty($queried_data) ) {
		return array();
	}

	if ( !is_array( $queried_data ) ) {
		$queried_data = array( $queried_data );
	}

	return $queried_data;
}

function crb_render_filter( $function_name, $param ) {

	$data = call_user_func( $function_name );

	$queried_data = crb_get_query_data( $param );
?>

	<ul class="list-checkboxes">

		<?php foreach ($data as $type):
			$index = crb_array_value_index( $type, $data );

			$active = '';

			if ( in_array($index, $queried_data) ) {
				$active = 'checked';
			}

			$handler = $param . '-' . $index;
		?>
			
			<li>

				<div class="checkbox">

					<input <?php echo $active; ?> name="<?php echo $param; ?>[]" value="<?php echo $index ?>" type="checkbox" id="<?php echo $handler; ?>" />
					<label for="<?php echo $handler; ?>"><?php echo $type; ?></label>

				</div><!-- /.checkbox -->

			</li>
			
		<?php endforeach ?>

	</ul><!-- /.checkboxes -->

	<?php
}


function crb_sorting_options() {

	$types = crb_sorting_data();

	$queried_data = crb_get_query_data( 'sort' );
?>

	<div class="form-col">
		<label for="field-sort" class="label-orange">SORT</label>

		<div class="select select-size1 sort-option">
			<select name="sort" id="field-sort" class="select">

				<?php foreach ($types as $type): 
					$option_id = crb_array_value_index( $type, $types );

					$active = '';

					if ( in_array($option_id, $queried_data) ) {
						$active = 'selected';
					}
				?>
					
					<option <?php echo $active; ?> value="<?php echo add_query_arg( 'sort', $option_id ); ?>"><?php echo $type; ?></option>
					
				<?php endforeach ?>

			</select>
		</div><!-- /.select -->
	</div><!-- /.form-col -->

	<?php
}


function crb_currency_options() {

	$types = crb_currency_data();

	$queried_data = crb_get_query_data( 'currency' );
?>
	
	<div class="form-col ">
		<label for="field-currency">Currency</label>

		<div class="select select-size2">
			<select name="field-currency" id="field-currency" class="select">
				
				<?php foreach ($types as $type): 
					$option_id = crb_array_value_index( $type, $types );

					$active = '';

					if ( in_array($option_id, $queried_data) ) {
						$active = 'selected';
					}
				?>
					
					<option <?php echo $active; ?> value="<?php echo $option_id; ?>"><?php echo $type; ?></option>
					
				<?php endforeach ?>

			</select>
		</div><!-- /.select -->
	</div><!-- /.form-col -->

	<?php
}


/*
	Slider filters
*/


function crb_slider_values( $min, $max, $arg_min, $arg_max ) {

	if ( isset($_GET[$arg_min]) ) {
		$min = $_GET[$arg_min];
	}

	if ( isset($_GET[$arg_max]) ) {
		$max = $_GET[$arg_max];
	}

	return array(
		'min' => $min,
		'max' => $max,
	);
}


function crb_slider_people() {

	$values = crb_slider_values( '4', '8', 'people-min', 'people-max' );

?>

	<li class="range-people-outer range-holder" data-min="4" data-max="8">
		<i class="ico-people"></i>

		<div class="range-slider range-people">
			<div id="range-people"></div>
		</div><!-- /.input-slider -->

		<input type="hidden" name="people-min" value="<?php echo $values['min']; ?>" class="value-min" />
		<input type="hidden" name="people-max" value="<?php echo $values['max']; ?>" class="value-max" />
	</li>

	<?php
}


function crb_slider_cases() {

	$values = crb_slider_values( '1', '5', 'cases-min', 'cases-max' );

?>

	<li class="range-case-big-outer range-holder" data-min="1" data-max="5">
		<i class="ico-case-big"></i>

		<div class="range-slider range-case-big">
			<div id="range-case-big"></div>
		</div><!-- /.input-slider -->

		<input type="hidden" name="cases-min" value="<?php echo $values['min']; ?>" class="value-min" />
		<input type="hidden" name="cases-max" value="<?php echo $values['max']; ?>" class="value-max" />
	</li>

	<?php
}


function crb_slider_bags() {

	$values = crb_slider_values( '1', '5', 'bags-min', 'bags-max' );

?>
	
	<li class="range-case-small-outer range-holder" data-min="1" data-max="5">
		<i class="ico-case-small"></i>

		<div class="range-slider range-case-small">
			<div id="range-case-small"></div>
		</div><!-- /.input-slider -->

		<input type="hidden" name="bags-min" value="<?php echo $values['min']; ?>" class="value-min" />
		<input type="hidden" name="bags-max" value="<?php echo $values['max']; ?>" class="value-max" />
	</li>

	<?php
}


function crb_slider_engine() {

	$values = crb_slider_values( '1', '3', 'engine-min', 'engine-max' );

?>

	<li class="range-engine-outer range-holder" data-min="1" data-max="3">
		<i class="ico-engine"></i>

		<div class="range-slider range-engine">
			<div id="range-engine"></div>
		</div><!-- /.input-slider -->

		<input type="hidden" name="engine-min" value="<?php echo $values['min']; ?>" class="value-min" />
		<input type="hidden" name="engine-max" value="<?php echo $values['max']; ?>" class="value-max" />
	</li>

	<?php
}