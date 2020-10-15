<?php

//extract data from the post
//set POST variables
$url = 'https://search.google.com/structured-data/testing-tool/validate';
$userAgent  = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:11.0) Gecko/20100101 Firefox/11.0';

if ( isset($_GET['url']) ) {
	$website = $_GET['url'];
} else {
	$website = 'https://www.linkedin.com/directory/people-d-13/';
}

echo '<h1>Analyzing ' . $website . '</h1>';

$fields_string = '';
$fields = array(
	'url' => $website,
);

//url-ify the data for the POST
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');

$fields_string = 'url=' . $website;

//open connection
$ch = curl_init();

$proxy = '62.210.254.87:7598';

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent); // empty user agents probably not accepted

curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

curl_setopt($ch, CURLOPT_PROXY, $proxy);     // PROXY details with port
// curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);   // Use if proxy have username and password
// curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); // If expected to call with specific PROXY type

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
// curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
// curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_AUTOREFERER, 1);

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);

if ( empty($result) ) {
	echo 'Missing result';
	exit;
}

$result = str_replace(")]}'", '', $result);
$data = json_decode($result, true);
	
$html = '';

if ( isset($data['html']) ) {
	$html = $data['html'];
	unset( $data['html'] );
}

echo '<pre>';
print_r( $data );
echo '</pre>';

if ( $html ) {
	echo '<h2>HTML</h2>';
	echo '<pre>';
	print_r( json_encode( $html ) );
	echo '</pre>';
}