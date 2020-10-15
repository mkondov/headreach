<?php 
	if ( empty($mail_data['score']) ) {
		return false;
	}

	$score = $mail_data['score'];

	if ( $score < 30 ) {
		$class = 'alert';
	} else if ( $score > 30 AND $score < 70 ) {
		$class = 'orange';
	} else {
		$class = 'success';
	}

	$sources = '';
	$api_data = json_decode( $mail_data['api_response'], true );

	switch ($mail_data['source']) {
		case 'emailhunter':
			$sources = $api_data['data']['sources'];
			$s_count = count($sources);
			$label = $s_count > 1 ? 'sources' : 'source';
			break;
	}
?>

<div class="progress email <?php echo $class; ?>"
	data-disable-hover="true"
	tabindex="1"
	aria-valuenow="<?php echo $score; ?>"
	aria-valuemin="0"
	aria-valuemax="100">
	<div class="progress-meter" style="width: <?php echo $score; ?>%"></div>
</div>

<div class="dropdown-pane accuracy-rate bottom" id="progress-dropdown-<?php echo $mail_data['id'] ?>" data-dropdown data-hover="true" data-hover-pane="true">
	<span class="rate-block <?php echo $class ?>"><?php echo $score; ?></span> Accuracy rate

	<?php if ( $sources ) : ?>
		
		<a class="float-right" data-open="sources-modal-<?php echo $mail_data['id'] ?>"><?php echo $s_count . ' ' . $label; ?></a>

	<?php endif; ?>
</div>

<?php if ( $sources ) : ?>

	<div class="reveal" id="sources-modal-<?php echo $mail_data['id'] ?>" data-reveal>
		<button class="close-button" data-close aria-label="Close modal" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		<h4><?php echo $s_count . ' ' . $label; ?> for <a href="mailto:<?php echo $mail_data['email']; ?>"><?php echo $mail_data['email']; ?></a></h4>

		<ul class="sources-list push-20">

			<?php foreach ($sources as $source): ?>
				
				<li><a target="_blank" href="<?php echo $source['uri']; ?>"><?php echo $source['domain']; ?></a> <small><?php echo $source['extracted_on']; ?></small></li>
				
			<?php endforeach ?>

		</ul>
	</div>

<?php endif; ?>