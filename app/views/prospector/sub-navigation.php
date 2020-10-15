<?php
	$webPath = Yii::$app->params['webPath'];

	$subpages = array(
		'namesearch' => 'Name',
		'companysearch' => 'Company',
		'websitesearch' => 'Domain',
		// 'postscan' => 'Scan a post',
		'advancedsearch' => 'Advanced search',
	);
?>

<div class="sub-masthead hide-for-small-only">
	<div class="row">
		<ul class="menu push-10 search-by-menu subpixel">

			<?php foreach ($subpages as $suburl => $sublabel): 
				$active = '';
				if ( $active_tab == $suburl ) {
					$active = 'class="active"';
				}
			?>
				
				<li>
					<a <?php echo $active; ?> href="<?php echo $webPath ?>/prospector/<?php echo $suburl ?>"><?php echo $sublabel; ?></a>
				</li>

			<?php endforeach ?>

		</ul>
	</div>
</div>