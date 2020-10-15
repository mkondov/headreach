<?php

error_reporting(0);
ini_set('max_execution_time', 600); //10 minute

require_once('smtp_validateEmail.class.php');
require_once('smtp-validate-email.php');

if ( isset($_POST['do_validate']) ) {

	// an optional sender
	$sender = 'dogo_stz@abv.bg';
	$email = $_POST['email'];

	echo '<h2>Fullcontact validation response</h2>';

	$url = 'http://api.fullcontact.com/v2/verification/email?email='. urlencode($email) .'&apiKey=6f5f6eaad68fa710';
	$contents = @file_get_contents( $url );
	if ( !empty($contents) ) {
		$api_result = json_decode( $contents, true );
		echo '<pre>';
		print_r( $api_result );
		echo '</pre>';
	}

	echo '<h2>Mailboxlayer validation response</h2>';

	$murl = 'https://apilayer.net/api/check?access_key=d93c26eeb5100afbad9b76479412c748&email=' . urlencode($email);
	$mcontents = @file_get_contents( $murl );
	if ( !empty($contents) ) {
		$mapi_result = json_decode( $mcontents, true );
		echo '<pre>';
		print_r( $mapi_result );
		echo '</pre>';
	}
	
	// instantiate the class
	// $SMTP_Validator = new SMTP_validateEmail();

	// turn on debugging if you want to view the SMTP transaction
	// $SMTP_Validator->debug = true;
	// do the validation

	// $results = $SMTP_Validator->validate(array(trim($email)), $sender);

	// if ( isset($results[$email]) ) {
	// 	echo 'Valid Email';
	// } else {
	// 	echo 'Invalid';
	// }

	// echo '<br /><br />';

	// echo '<h2>Validation Method 2</h2>';
	// $validator = new SMTP_Validate_Email($email, $sender);
	// $results = $validator->validate();

	// echo '<pre>';
	// print_r( $results );
	// echo '</pre>';
	// exit;

}