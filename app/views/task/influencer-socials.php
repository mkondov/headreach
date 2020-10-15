<?php
	use app\controllers\ProspectorController;

	$webPath = Yii::$app->params['webPath'];
	$socials_map = ProspectorController::getSocials();

	$name = $influencer['name'];
	$name_pieces = explode(' ', $name);
	$first_name = $name_pieces[0];

	$socials = '';
	if ( $influencer['person_social'] ) {
		$socials = json_decode( $influencer['person_social'] );
	}

	$emails = $influencer['emails_data'];
?>

<td data-th="Email" <?php echo ( ($emails AND $charge == true) ? 'class="data-entry-found"' : '' ); ?> >
	<?php
		$data = [
			'emails' => $emails,
			'first_name' => $first_name,
			'person_data' => $influencer,
		];
		echo Yii::$app->controller->renderPartial('/partials/list-emails', $data);
	?>
</td>

<td class="social">
	<div class="clearfix">

		<?php if ( $socials ) : ?>

			<ul class="social-profiles-menu">

			<?php foreach ($socials as $social): 
				preg_match( '~^(.*\/\/[^\/?#]*).*$~', $social, $domain );
				$root_domain = preg_replace('~^https?://~', '', $domain[1]);

				$icon = 'other';

				foreach ($socials_map as $pd_icon => $value) {
					if ( preg_match('~'. $value .'~', $root_domain) ) {
						$icon = $pd_icon;
					}
				}
			?>
				
				<li>
					<a target="_blank" href="<?php echo $social; ?>">
						<img src="https://www.fullcontact.com/wp-content/themes/fullcontact/assets/images/social/<?php echo $icon; ?>.png">
					</a>
				</li>

			<?php endforeach ?>

			</ul>

		<?php endif; ?>

	</div>
</td>