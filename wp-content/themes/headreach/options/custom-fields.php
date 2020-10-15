<?php

/*
	Page Data
*/
Carbon_Container::factory('custom_fields', __('Page Data', 'crb'))
	->show_on_post_type('page')
	->add_fields(array(
		Carbon_Field::factory('checkbox', 'crb_add_account_styles'),
		Carbon_Field::factory('checkbox', 'crb_show_footer'),
		// Carbon_Field::factory('text', 'crb_tab_section_description'),

		// Carbon_Field::factory('complex', 'crb_page_tabs')->add_fields(array(
		// 	Carbon_Field::factory('text', 'title')
		// 		->set_required( true ),
		// 	Carbon_Field::factory('Rich_text', 'text')
		// 		->set_required( true ),
		// )),
	));

/*
	Users Data
*/
Carbon_Container::factory('user_meta', __('Additional User Data', 'crb'))
	->add_fields(array(
		Carbon_Field::factory('checkbox', 'crb_user_activated')
			->help_text( 'This field indicates whether the user has actually confirmed his/her email address' ),
		Carbon_Field::factory('textarea', 'crb_cancellation_reason')
			->set_height( 100 ),
		Carbon_Field::factory('textarea', 'crb_cancellation_reason_description', 'Cancellation Description')
			->set_height( 100 ),
	));

Carbon_Admin_Columns_Manager::modify_columns('user')
  // ->remove( array('email', 'role', 'posts') )
  ->add( array(
  		Carbon_Admin_Column::create('Has Subscription?')
  			// ->set_sort_field('has_subscription')
			->set_callback('crb_column_active_subscriber'),
		Carbon_Admin_Column::create('Active')
			->set_width( 50 )
			->set_callback('crb_column_active_user'),
		Carbon_Admin_Column::create('Additional info')
			->set_callback('crb_column_get_user_registration_date'),
	 ));

function crb_column_active_subscriber( $user_id ) {
	ob_start();
?>
		
	<div class="woocommerce_active_subscriber">

		<?php if ( wcs_user_has_subscription( $user_id, '', 'active' ) ) : ?>
			<div class="active-subscriber"></div>
		<?php else : ?>
			<div class="inactive-subscriber">-</div>
		<?php endif; ?>

	</div>

	<?php

	return ob_get_clean();
}


function crb_column_active_user( $user_id ) {
	ob_start();

	$status = carbon_get_user_meta( $user_id, '_crb_user_activated' );
	$color = ( $status ? '#33b226' : '#dc1b1b' );

	?>

	<p style="color: <?php echo $color; ?>"><?php echo ( $status ? 'Yes' : 'No' ); ?></p>

	<?php
	return ob_get_clean();
}

function crb_column_get_user_registration_date( $user_id ) {
	global $app_db;

	$jobs = $app_db->get_results("SELECT * FROM hr_jobs WHERE wp_user_id = $user_id");
	$searches = $app_db->get_results("SELECT * FROM hr_social_searches WHERE wp_user_id = $user_id");

	$found = 0;
	if ( $searches ) {
		foreach ($searches as $search) {
			if ( $search->is_charged ) {
				$found++;
			}
		}
	}

	if ( empty($found) AND empty($searches) ) {
		$find_rate = 'N/A';
	} else {
		$find_rate = $found / count($searches);
		$find_rate = ceil($find_rate * 100) . '%';
	}

	ob_start(); ?>

	<table class="admin-statistics-table">
		<thead>
			<th>Total Searches</th>
			<th>Find attempts</th>
			<th>Credits used</th>
			<th>Rate</th>
		</thead>
		<tbody>
			<tr>
				<td><strong><?php echo count($jobs); ?></strong></td>
				<td><strong><?php echo count($searches); ?></strong></td>
				<td><strong><?php echo $found; ?></strong></td>
				<td><strong><?php echo $find_rate; ?></strong></td>
			</tr>
		</tbody>
	</table>

	<?php

	return ob_get_clean();
}