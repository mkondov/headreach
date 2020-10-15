<html>
<head>
<link href='http://fonts.googleapis.com/css?family=Roboto:400,300,500,700,900' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="css/style.css">
<title>Email SMTP validator and permutator </title>
</head>
<body>

	<div class="fix structure header_area">
		<div class="fix header"></div>
	</div>
	<div class="fix structure csv_form_area">
		<div class="fix csv_form">
			<h2>CSV Email Validator </h2>
			<?php if(isset($_GET['error'])) {?>
			<p style="display:inline;color:red">Please select a csv email file</p>
			<?php } else { ?>
			<p style="display:none;color:red">Please select a csv email file</p>
			<?php } ?>
			<form method="post" action="csv_email_validator.php" enctype="multipart/form-data">
				<p>Browse Email CSV file :<input type="file" name="email_file" id="email_file"></p>		
				<p><input type="submit" name="Validate" class="button" value="Validate"></p>
			</form>
		</div>
	</div>
	<div class="fix structure email_permutator_area">
		<div class="fix email_permutator">
			<h2>Email Permutator </h2>
			<?php if(isset($_GET['emailPermutatorError'])) {?>
			<p style="display:inline;color:red">Sorry brah....All fields must be populated!!</p>
			<?php } else { ?>
			<p style="display:none;color:red">Sorry brah....All fields must be populated!!</p>
			<?php } ?>
			<form method="post" action="email_permutator.php">
				<p>First Name : <input type="text" name="fname"></p>		
				<p>Last Name : <input type="text" name="lname"></p>		
				<p>Domain <span>:</span> <input type="text" name="domain"></p>		
				<p><input type="submit" name="search" class="button" value="Search"></p>
			</form>
		</div>
	</div>
	<div class="fix structure footer_area">
		<div class="fix footer"></div>
	</div>
<body>
<html>
