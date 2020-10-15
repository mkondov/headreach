<?php
	$validation_data = json_decode( $mail_data['validation_data'], true );
	$is_valid = $validation_data['smtp_check'];
	$is_catch_all = $validation_data['catch_all'];

	if ( $is_valid AND !$is_catch_all ) {
		$is_valid_image = 'icn-tick-green';
		$is_valid_sub_image = 'icn-tick-green-circle';
		$title_label = 'Valid Email';
		$box_class = 'success';
	} else if ( $is_catch_all ) {
		$is_valid_image = 'icn-alert';
		$is_valid_sub_image = 'icn-alert';
		$title_label = 'Risky Email';
		$box_class = 'alert';
	} else {
		$is_valid_image = 'icn-x-gray';
		$is_valid_sub_image = 'icn-x-circle-gray';
		$title_label = 'Invalid Email';
		$box_class = 'invalid';
	}

	$fields = array(
		'format_valid' => 'Valid format',
		'role' => 'Generic (role) email',
		'free' => 'Free email',
		'disposable' => 'Disposable email',
		'smtp_check' => 'Mail server accepted the email',
		'mx_found' => 'MX recrods exists on this domain',
		'catch_all' => 'Mail server accepts all emails (catch-all)',
	);
?>

<div data-toggle="verification-dropdown-<?php echo $mail_data['id']; ?>">
	<a class="email-in-table <?php echo $box_class; ?>" href="mailto:<?php echo $mail_data['email']; ?>">
		<?php echo $mail_data['email']; ?>
		<img src="<?php echo $webPath; ?>/img/<?php echo $is_valid_image ?>.svg" width="15">
	</a>

	<div class="dropdown-pane verification <?php echo $box_class ?> bottom right" id="verification-dropdown-<?php echo $mail_data['id'] ?>" data-dropdown data-hover="true" data-hover-pane="true">
		<div class="verification-email-type <?php echo $box_class ?> text-center">
			<img src="<?php echo $webPath ?>/img/<?php echo $is_valid_sub_image ?>.svg">
			<?php echo $title_label; ?>
		</div>

		<table>

			<?php foreach ($fields as $key => $label): 
				$class = 'alert';
				$msg = 'No';

				if ( $validation_data[$key] ) {
					$class = 'success';
					$msg = 'Yes';
				}
			?>
				
				<tr>
					<td><?php echo $label; ?></td>
					<td class="<?php echo $class ?>"><?php echo $msg; ?></td>
				</tr>
				
			<?php endforeach ?>

			<?php if ( $box_class == 'invalid' ) : ?>

				<tr>
					<td>
						<span data-tooltip data-disable-hover="false" tabindex="1" title="We don't recommend emailing invalid addresses. However, there's a tiny chance (5-10%) that a valid email shows up as invalid by mistake. We return invalid email addresses for free as you may find them useful.">Why we show invalid emails?</span>
					</td>
					<td>
						<span class="badge info-dark" data-tooltip  data-disable-hover="false" tabindex="1" title="We don't recommend emailing invalid addresses. However, there's a tiny chance (5-10%) that a valid email shows up as invalid by mistake. We return invalid email addresses for free as you may find them useful."></span>
					</td>
				</tr>

			<?php endif; ?>

		</table>

		<?php if ( $box_class == 'invalid' ) : 
			$name = $person_data['name'];
			// $company = $person_data['company'];
		?>

			<div class="verification-meta">
				<a href="mailto:contact@headreach.com?subject=Find email for <?php echo $name; ?>&body=Could you find the right email for <?php echo $name; ?>.">Help me find this email</a><a href="#">Learn about verification</a>
			</div>

		<?php else : ?>

			<div class="verification-meta">
				<a href="mailto:contact@headreach.com?subject=Report bad email — <?php echo $mail_data['email']; ?>&body=Could you check <?php echo $mail_data['email']; ?>. It's wrong.">Report bad email</a><a href="#">Learn about verification</a>
			</div>

		<?php endif; ?>

	</div>
	
</div>