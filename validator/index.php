<html>
<head>
<link href='http://fonts.googleapis.com/css?family=Roboto:400,300,500,700,900' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="css/style.css">
<title>Email SMTP validator and permutator </title>
	
	<style type="text/css">
		form { }
		form .field { padding: 8px 5px; margin-left: 10px; }
	</style>

</head>
<body>

	<div class="fix structure header_area">
		<div class="fix header"></div>
	</div>
	<div class="fix structure email_permutator_area">
		<div class="fix email_permutator">
			<h2>Validate email</h2>
			<form method="post" action="email_validator.php">
				<p>Email address: <input class="field" type="text" name="email"></p>
				<p><input type="submit" name="search" class="button" value="Validate"></p>
				<p><input type="hidden" name="do_validate" value="1"></p>
			</form>
		</div>
	</div>
	<div class="fix structure footer_area">
		<div class="fix footer"></div>
	</div>

<body>
<html>