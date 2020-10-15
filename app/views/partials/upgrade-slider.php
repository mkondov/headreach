<div class="upgrade-slider clearfix">
	<div class="row">
		
		<button class="close-button" data-close="" aria-label="Close reveal" type="button">
			<span aria-hidden="true">×</span>
		</button>
		
		<div class="small-12 medium-8 column">		
			<h5>You've used <strong><?php echo $credits['used']; ?></strong> of <strong><?php echo $credits['total']; ?></strong> free credits</h5>
			<p>To get more credits upgrade your plan now. Plans start at just $19/month. No long term contracts. Cancel at any time.</p>
		</div>
	
		<div class="small-12 medium-4 column credits">
			<div class="clearfix">
				<span class="highlight"><?php echo $credits['used']; ?></span> used  <span class="divider">/</span> <span class="highlight"><?php echo $credits['total']; ?></span> total
			</div>
			
			<div class="progress" role="progressbar" tabindex="0" aria-valuenow="<?php echo $credits['percentage_used'] ?>" aria-valuemin="0" aria-valuemax="100">
				<div class="progress-meter" style="width: <?php echo $credits['percentage_used'] ?>%"></div>
			</div>
			
			<div class="button orange"><a style="color: #ffffff" href="<?php echo $websiteURL ?>/my-account/subscription/">Upgrade plan</a></div>
		</div>	
	
	</div>
</div>