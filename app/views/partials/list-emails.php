<?php
	if ( empty($emails) ) {
		echo '&nbsp;';
		return;
	}

	$webPath = Yii::$app->params['webPath'];

	$class = 'show-for-large';
	if ( isset($in_popup) ) {
		$class = '';
	}
?>

<?php foreach ($emails as $email): 
	$id = md5(uniqid());
	$email['id'] = $id;

	$validation_data = $email['validation_data'];
?>
	
	<?php if ( $validation_data ) : ?>

		<?php
			$data = [
				'mail_data' => $email,
				'webPath' => $webPath,
				'person_data' => $person_data,
			];
			echo Yii::$app->controller->renderPartial('/partials/validation-bar', $data);
		?>

	<?php else : ?>

		<div data-toggle="progress-dropdown-<?php echo $id; ?>">
			<a class="<?php echo $class; ?>" href="mailto:<?php echo $email['email']; ?>"><?php echo $email['email']; ?></a>

			<?php 
				$data = [
					'mail_data' => $email
				];
				echo Yii::$app->controller->renderPartial('/partials/progress-bar', $data);
			?>
		</div>

	<?php endif; ?>

<?php endforeach ?>

<?php if ( !isset($in_popup) OR empty($in_popup) ) : ?>

<div class="text-center subpixel hide-for-medium">
	<a href="mailto:<?php echo $emails[0]['email']; ?>" class="button text-center small">
		<img width="17" class="envelope" src="<?php echo $webPath; ?>/img/icn-envelope.svg">Email <?php echo $first_name; ?>
	</a>
</div>

<?php endif; ?>