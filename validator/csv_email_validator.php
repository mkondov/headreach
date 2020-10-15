<?php
	error_reporting(0);
	ini_set('max_execution_time', 600); //10 minute
	/**
	 * Example 1
	 * Validate a single Email via SMTP
	 */


	// include SMTP Email Validation Class
	if (isset($_POST['Validate'])){
	
		if($_FILES["email_file"]["size"]==0 || empty($_FILES["email_file"])) {
			header('Location: index.php?error=true');
			exit();
		}
		require_once('smtp_validateEmail.class.php');
		
		$valid_email = array();
		$invalid_email = array();

		$target_dir = "uploads/";
		if (!file_exists($target_dir )) {
			mkdir($target_dir , 0777, true);
		}
		$file_dir = $target_dir . basename($_FILES["email_file"]["name"]);
		$uploadOk = 1;

		$fileType = pathinfo($file_dir,PATHINFO_EXTENSION);

		if (move_uploaded_file($_FILES["email_file"]["tmp_name"], $file_dir)) {

			// an optional sender
			$sender = 'someemail@youremail.com';
			// instantiate the class
			$SMTP_Validator = new SMTP_validateEmail();
			// the email to validate
			$file = fopen($file_dir ,"r");

			$data = fgetcsv($file, 1000, ";");

			foreach ($data as $email){
				// turn on debugging if you want to view the SMTP transaction
				//echo $email;
				//$SMTP_Validator->debug = true;
				// do the validation
				$results = $SMTP_Validator->validate(array(trim($email)), $sender);

				if($results[$email])
					$valid_email[] = trim($email);
				else
					$invalid_email[] = trim($email);
				//echo "<li>".$email."</li>";
			}
			fclose($file);
		}	

		//remove upload file
		array_map('unlink', glob($target_dir."*"));

	}
	?>
<html>
<head>
<link rel="stylesheet" href="css/style.css">
<title>Email SMTP validator and permutator </title>
</head>
<body>
	<div class="fix structure header_area">
		<div class="fix header"></div>
	</div>
	<div class="fix structure csv_form_area" style="border-bottom:none">
		<div class="fix csv_form">
            <?php
			  if($fileType != "csv" ) {
				die("What are you trying to feed me (✖╭╮✖), only csv files are allowed.");
			}
			?>
			<h2>Valid Email:</h2>
			<ol>
			<?php
			foreach ($valid_email as $email){
				echo "<li><img src='images/ok.png' style='height:13px' />".$email."</li>";
			}
			?>
			</ol>

			<h2>Invalid Email:</h2>
			<ol>
			<?php
			foreach ($invalid_email as $email){
				echo "<li><img src='images/error.png' style='height:13px' />".$email."</li>";
			}
			?>
			</ol>
		</div>
	</div>
	<div class="fix structure email_permutator_area">
		<div class="fix email_permutator">
		</div>
	</div>
	<div class="fix structure footer_area">
		<div class="fix footer"></div>
	</div>
<body>
<html>