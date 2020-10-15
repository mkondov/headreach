<?php

if ( !isset($_SERVER['REMOTE_ADDR']) ) {
	return false;
}

die( $_SERVER['REMOTE_ADDR'] );

// $browser = get_browser(null, true);

echo '<pre>';
print_r( $browser );
print_r( $_SERVER );
print_r( $_COOKIE );
echo '</pre>';
exit;