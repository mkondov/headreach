<?php 
	if ( empty($parameters) ) {
		return false;
	}

	// No tags
	if ( !isset($parameters[$key]) OR empty($parameters[$key]) ) {
		return false;
	}

	$tags = $parameters[$key];
?>

<ul class="tags clearfix subpixel">
	
	<?php foreach ($tags as $tag): ?>
		
		<li>
			<span class="tag"><a data-value="<?php echo $tag; ?>" href="#" class="close">×</a><?php echo $tag ?></span>
		</li>

	<?php endforeach ?>

</ul>