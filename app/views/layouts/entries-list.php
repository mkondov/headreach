<!-- Table Start -->
<table class="results-table push-top-10 tableWithFloatingHeader">
	<thead>
		<tr>
			<th width="50%">Mention</th>
			<th width="25%">Company</th>
			<th width="25%">Person(s)</th>
		</tr>
	</thead>
	<tbody>

		<?php foreach ($influencers as $url => $people): 
			$total_results = count($people);
		?>
			
			<tr id="task-id-<?php echo $active_task['id']; ?>-<?php echo $url; ?>">
				<td data-th="Mention"><a href="#"><?php echo $url; ?></a></td>
			    <td data-th="Company"><?php echo $url; ?></td>

			    <?php if ( $total_results > 1 ) : ?>

			    	<td data-th="Person(s)">
				    	<a class="profile-link" href="#">
							<div class="profile-link-wrapper">

								<?php foreach ($people as $person): 
									$photo = str_replace('/home/dogostz/webapps/headreach/web/', '', $person['photo_path']);
									$photo = 'http://headreach.info/' . $photo;
								?>
									
					    			<span style="background-image: url(<?php echo $photo; ?>)" class="avatar">
					    				<?php echo $person['first_name'] . ' ' . $person['last_name']; ?>
					    			</span>

								<?php endforeach ?>

							</div>
						</a>
			    	</td>

			    <?php elseif ( $total_results == 1 ) : ?>


			    	<?php foreach ($people as $person): ?>

				    	<td data-th="Person(s)">
				    		<?php echo $person['first_name'] . ' ' . $person['last_name']; ?>
				    	</td>

			    	<?php endforeach; ?>

			    <?php endif; ?>

			    <?php
			    	// Remove json data from the returned results
			    	$tmp_results = array();
			    	foreach ($people as $p) {
			    		unset($p['full_contact_person_json']);
			    		unset($p['linkedin_json']);

			    		if ( !empty($p['title']) ) {
			    			$p['title'] = strip_tags($p['title']);
			    		}

			    		if ( !empty($p['bio']) ) {
			    			$p['bio'] = strip_tags($p['bio']);
			    		}

						if ( !empty($p['person_social']) ) {
							$p['person_social'] = json_decode($p['person_social'], true);
						}

						if ( !empty($p['company_social']) ) {
							$p['company_social'] = json_decode($p['company_social'], true);
						}

			    		$tmp_results[] = $p;
			    	}
			    ?>

			    <td class="results-holder" style="display: none;">
			    	<?php echo json_encode($tmp_results); ?>
			    </td>

			</tr>
			
		<?php endforeach ?>

	</tbody>
</table>

<!-- Table End -->