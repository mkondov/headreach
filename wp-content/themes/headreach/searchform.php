<form role="search" method="get" class="searchform" action="<?php echo home_url('/'); ?>"> 
	<div>
		<label class="screen-reader-text" for="s"><?php _e('Search for:', 'crb'); ?></label> 
		<input type="text" class="searchfield" value="" name="s" id="s" /> 
		<input type="submit" class="searchsubmit" value="<?php echo esc_attr(__('Search', 'crb')); ?>" />
	</div> 
</form>