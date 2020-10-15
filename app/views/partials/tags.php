<?php 
	if ( empty($parameters) ) {
		return false;
	}

	$parameters = unserialize($parameters);
?>

<ul class="tags clearfix subpixel">

	<?php foreach ($parameters as $param => $param_data): 
		$label = ucfirst($param);
		if ( $label == 'Start_date' ) {
			$label = 'Job Start Date';
		}

		if ( $label == 'Current_only' ) {
			$label = 'Only current positions';
		}
	?>
		
		<li>
			<span class="tag">
				<span href="#" class="close"></span>
				<span class="tag-label"><?php echo $label; ?></span>
				
				<?php
					foreach ($param_data as $i => $pd) {
						echo $pd;
						if ( count( $param_data ) > ($i+1) ) {
							echo ', ';
						}
					}
				?>
			</span>
		</li>

	<?php endforeach; ?>

</ul>