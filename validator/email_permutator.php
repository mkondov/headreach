<?php
	error_reporting(0);
	ini_set('max_execution_time', 600); //10 minute
	if (isset($_POST['search'])){
	  if(trim($_POST['fname']) =="" || trim($_POST['lname']) == "" ||  trim($_POST['domain']) == ""){
		header('Location: index.php?emailPermutatorError=true');
		exit();
	  }
	  require_once('permutatorEmail.class.php');
	  require_once('smtp_validateEmail.class.php');
	  //html tag element filter
	  $fristName = filter_var(trim($_POST['fname']), FILTER_SANITIZE_STRIPPED);
	  $lastName = filter_var(trim($_POST['lname']), FILTER_SANITIZE_STRIPPED);
	  $domainName = filter_var(trim($_POST['domain']), FILTER_SANITIZE_STRIPPED);
	  
	  //add .com if not exist
	  // if (!preg_match('/.com/',$domainName))
	  // $domainName=$domainName.".com";

	  $fristName = strtolower($fristName);
	  $lastName = strtolower($lastName);
	  
	  $permutator = new EmailPermutator($fristName, $lastName, $domainName );

	  $permutator->addSpecialCharBetweenNames();
	  $permutator->addEmailOnlyLnameORfname();
	  $permutator->addEmailByFirstCharCombination();

	  echo '<pre>';
	  print_r( $permutator );
	  echo '</pre>';
	  exit;	  
	}
?>

<html>
<head>
<link rel="stylesheet" href="css/style.css">
<title>Email SMTP validator and permutator </title>
</head>
<body>
	<div class="fix structure header_area">
		<div class="fix header" id="header"></div>
	</div>
	<div class="fix structure csv_form_area" style="border-bottom:none">
		<div class="fix csv_form">
			<h2>Email Permutator:</h2>
			<ul> 
			<?php
				foreach($permutator->email_permutators as $email){
					
					// an optional sender
					$sender = 'shamim_ict0754@yahoo.com';
					// instantiate the class
					$SMTP_Validator = new SMTP_validateEmail();
					// turn on debugging if you want to view the SMTP transaction
					//echo $email."<br>";
					//$SMTP_Validator->debug = true;
					///*
					$results = $SMTP_Validator->validate(array(trim($email)), $sender);
					if($results[$email])
						echo "<li class='valid'><img src='images/ok.png' style='height:13px' />".$email." is valid</li>";
					else
						echo "<li class='invalid'><img src='images/error.png' style='height:13px' />".$email." is invalid</li>";
					//*/	
						
				}  
				  
			?>
			</ul>
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



