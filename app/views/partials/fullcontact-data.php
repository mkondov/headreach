<?php
	if ( empty($fullcontact_data) ) {
		return false;
	}

	$data = json_decode( $fullcontact_data );
	$data = json_decode( $data, true );

	$websites = ( isset($data['contactInfo']['websites']) ? $data['contactInfo']['websites'] : array() );
	$organizations = ( isset($data['organizations']) ? $data['organizations'] : array() );
	$socials = ( isset($data['socialProfiles']) ? $data['socialProfiles'] : array() );
	$footprints = ( isset($data['digitalFootprint']['topics']) ? $data['digitalFootprint']['topics'] : array() );
?>

<?php if ( $websites ) : ?>

	<tr>
		<td>Websites</td>
		<td>
			<?php foreach ($websites as $site): ?>
					
				<a style="display: block" target="_blank" href="<?php echo $site['url']; ?>"><?php echo $site['url']; ?></a>

			<?php endforeach ?>			
		</td>
	</tr>

<?php endif; ?>

<?php if ( $socials ) : 
	$show_socials = false;
	foreach ($socials as $social) {
		if ( isset($social['followers']) ) {
			$show_socials = true;
		}
	}

	if ( $show_socials ) :
?>

	<td>Social presence</td>
	<td>
		<ul class="social-profiles-menu">

			<?php foreach ($socials as $social): 
				if ( !isset($social['followers']) ) {
					continue;
				}

				$typeId = $social['typeId'];

				if ( preg_match('~google~', $typeId) ) {
					$typeId = 'googleplus';
				}

				switch ($typeId) {
					case 'linkedin':
						$label = 'connections';
						break;
					
					default:
						$label = 'followers';
						break;
				}
			?>
				
				<li>
					<a class="social-presence-tag <?php echo $typeId; ?> subpixel" target="_blank" href="<?php echo $social['url']; ?>"><img src="https://www.fullcontact.com/wp-content/themes/fullcontact/assets/images/social/<?php echo $typeId; ?>.png"><?php echo $social['followers'] . ' ' . $label; ?></a>
				</li>

			<?php endforeach ?>

		</ul>
	</td>

	<?php endif; ?>
<?php endif; ?>

<?php if ( $organizations ) : ?>

	<tr>
		<td>Work history</td>
		<td>
			<ul class="organizations">
				
				<?php foreach ($organizations as $organization): 
					$start = ( isset($organization['startDate']) ? $organization['startDate'] : '' );
					$end = ( isset($organization['endDate']) ? $organization['endDate'] : '' );
					
					if ( isset($organization['current']) ) {
						$end = 'present';
					} elseif ( !empty($end) ) {
						$end = date( 'F Y', strtotime($end) );
					}
				?>
					
					<li>
						<strong><?php echo $organization['name']; ?></strong>
						<?php if ( $start ) : ?>
							<?php echo date( 'F Y', strtotime($start) ) ?> - <?php echo $end; ?><br>
						<?php endif; ?>

						<?php echo $organization['title']; ?>
					</li>

				<?php endforeach ?>

			</ul>
		</td>
	</tr>

<?php endif; ?>

<?php if ( $footprints ) : ?>
	
	<tr>
		<td>Interests</td>
		<td>
			<?php
				foreach ($footprints as $i => $footprint) {
					echo $footprint['value'] . ( ($i+1) < count($footprints) ? ', ' : '' );
				}
			?>
		</td>
	</tr>

<?php endif; ?>