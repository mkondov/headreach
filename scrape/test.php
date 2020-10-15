<?php

//extract data from the post
//set POST variables
$url = 'http://dynupdate.no-ip.com/ip.php';
$userAgent  = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:11.0) Gecko/20100101 Firefox/11.0';

// $proxy = '144.217.121.104:222';
$proxy = '192.129.254.133:9001';
$proxyauth = 'dogo_stz:dogo1234';

// 62.210.254.87:7598
// 62.210.254.87:7599
// 62.210.254.87:7600
// 62.210.254.87:7601
// 62.210.254.87:7602
// 62.210.254.87:7603
// 62.210.254.87:7604
// 62.210.254.87:7605
// 62.210.254.87:7606
// 62.210.254.87:7607
// 62.210.254.87:7608
// 62.210.254.87:7609
// 62.210.254.87:7610
// 62.210.254.87:7611
// 62.210.254.87:7612
// 62.210.254.87:7613

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
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

echo '<pre>';
print_r( $result );
echo '</pre>';
exit;