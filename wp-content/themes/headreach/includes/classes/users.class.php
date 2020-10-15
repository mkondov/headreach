<?php

# Initialize the class
add_action( 'init', array( 'AE_User', 'init' ) );

/**
* A Basic class for managing user registrations and user logins
* Requires Carbon Custom Fields
*/
class AE_User {

	// Set a cookie verification
	public $rp_cookie;

	
	# Build a new instance of the class
	public static function init() {
		$this_class = new self();
	}
		
	
	# The main constructor
	function __construct() {

		$this->rp_cookie = 'wp-resetpass-' . COOKIEHASH;

		# Login Actions
		add_action( 'wp_authenticate', array( $this, 'login_with_email_address' ) );

		# Remote Login
		add_action( 'wp_ajax_nopriv_login', array( $this, 'login' ) );
		add_action( 'wp_ajax_login', array( $this, 'login' ) );

		# Register Actions
		add_action( 'wp_ajax_nopriv_register_user', array( $this, 'register' ) );
		add_action( 'wp_ajax_register_user', array( $this, 'register' ) );

		# Update Password Actions
		add_action( 'wp_ajax_nopriv_lost_password', array( $this, 'lost_password' ) );
		add_action( 'wp_ajax_change_password', array( $this, 'change_password' ) );
		add_action( 'wp_ajax_nopriv_change_password', array( $this, 'change_password' ) );

		// redefine wp_new_user_notification as soon as all plugins are loaded
		// add_action( 'plugins_loaded', array( $this, 'new_user_notifiaction' ) );

		# Add an additional user role
		// add_role('patient', 'Patient', array(
		// 	'read' 			=> true,
		// 	'edit_posts' 	=> true,
		// 	'delete_posts' 	=> true,
		// 	'upload_files' 	=> true
		// ));

	}


	# Login with an email address
	public function login_with_email_address($username) {
		
		$user = get_user_by( 'email', $username );

		if ( !empty($user->user_login) ) {
			$username = $user->user_login;
		}

		return $username;
	}


	public function login() {

		// check the nonce, if it fails the function will break
		// check_ajax_referer( 'ajax-login-nonce', 'security' );

		if ( is_user_logged_in() ) {
			$this->show_message( 'alert', 'ERROR: You are already logged in!' );
		}

		$validator = new AE_Validator($_POST, array(
			'email' 		=> 'required|email',
			'user_password' => 'required',
		));

		$response = array(
			'status' => $validator->passes() ? 'success' : 'alert'
		);

		if ( !$validator->passes() ) {
			$response['message'] = $validator->get_errors();
			die( json_encode( $response ) );
		}

		if ( !email_exists( $_POST['email'] ) ) {
			$this->show_message( 'alert', 'ERROR: We couldn\'t find this email address.' );
		}

		if ( $_POST['email'] == 'wcolvard@veuxmarketing.com' ) {
			$response['message'] = array( 'Your account has been suspended!' );
			die( json_encode( $response ) );
		}

		$creds = array(
			'user_login'    => $this->login_with_email_address( esc_attr($_POST['email']) ),
	    	'user_password' => esc_attr( $_POST['user_password'] ),
		);

		$user_id = $this->wp_login( $creds );

		$is_user_activated = get_user_meta( $user_id, '_crb_user_activated', true );

		if ( !$is_user_activated ) {
			$activate_msg = array( 'Almost done! We\'ve sent a confirmation email to <strong>'. $_POST['email'] .'</strong>. Click the link in the email to confirm this address.' );
			$response['status'] = 'alert';
			$response['message'] = $activate_msg;
			wp_logout();
			die( json_encode( $response ) );
		}

		$login_url = get_option( 'crb_login_redirect_url' );

		$returned_data = array(
			'status' 		=> 'success',
			'message' 		=> array( 'Logging you in' ),
			'redirect_url' 	=> add_query_arg( 'new-login', '1', $login_url ),
		);

		die( json_encode( $returned_data ) );
	}

	
	# Register user
	public function register() {

		if ( is_user_logged_in() ) {
			$this->show_message( 'alert', 'You are already logged in the main website.' );
		}
	
		// check the nonce, if it fails the function will break
		// check_ajax_referer( 'ajax-register-nonce', 'security' );

		$errors = array();

		$validator = new AE_Validator($_POST, array(
			'first_name' 		=> 'required',
			'last_name' 		=> 'required',
			'phone_number' 		=> 'required|numeric|string_min_length:5',
			'email_address' 	=> 'required|email',
			'user_password' 	=> 'required|string_min_length:5',
		));

		if ( !$validator->passes() ) {
			$errors = $validator->get_errors();
		}

		$whitelist = array(
			'122.53.99.235'
		);

		if ( isset($_SERVER['REMOTE_ADDR']) ) {
			$users_ip = $_SERVER['REMOTE_ADDR'];
			if ( ip_registered($users_ip) AND !in_array($users_ip, $whitelist) ) {
				$errors['ip_registered'] = 'Your IP address is already registered within our system';
			}
		}

		if ( isset($_POST['email_address']) AND !empty($_POST['email_address']) ) {
			if ( $this->isNotCompanyEmail( strtolower($_POST['email_address']) ) ) {
				$errors['email_not_valid'] = 'Please use your company email address. Generic emails, such as @gmail.com, @yahoo.com are not allowed.';
			}
		}

		if ( !empty($errors) ) {
			$response = array(
				'status' => 'alert',
				'message' => $errors,
			);
			die( json_encode( $response ) );
		}


		// We should send the confirmation email at this point
		// We should be also checking whether the new user could be actually registered

		$role = 'subscriber';
		$user_email = strtolower($_POST['email_address']);

		$user_data = array(
			'user_login' 	=> $user_email,
			'first_name' 	=> $_POST['first_name'],
			'last_name' 	=> $_POST['last_name'],
			'role' 			=> $role,
			'user_email' 	=> $user_email,
			'user_pass' 	=> $_POST['user_password']
		);

		$uid = wp_insert_user( $user_data );

		// Check if there are issues with the new user
		if ( !is_wp_error( $uid ) ) {

			// Register to active campaign
			$api_url = 'https://headreach.api-us1.com';
			$api_key = 'd22e414003e03b037b5cad6f48a21375c9b1d87ee06477d6dedbce5233cb20f491291212';

			$ac = new ActiveCampaignWordPress( $api_url, $api_key );

			$method = 'contact/add';
			$params = array(
				'email' => $user_email,
				'first_name' => $_POST['first_name'],
				'last_name' => $_POST['last_name'],
				'p[7]' => 7,
				'status[7]' => 1,
				'instantresponders[7]' => 1,
			);

			if ( isset($_POST['phone_number']) AND !empty($_POST['phone_number']) ) {
				$params['phone'] = $_POST['phone_number'];
			}

			$ac_result = $ac->api( $method, $params );
			if ( $ac_result AND isset($ac_result->subscriber_id) ) {
				update_user_meta( $uid, '_ac_subscriber_id', $ac_result->subscriber_id );
			}

			// Indicate that the user is not registered
			update_user_meta( $uid, '_crb_user_activated', '' );

			// Optional Metas
			$metas = array(
				// 'company' => '_ae_company',
				'phone_number' => 'billing_phone',
			);

			foreach ($metas as $post_key => $meta_key) {
				$value = $_POST[$post_key];

				if ( empty($value) ) {
					continue;
				}

				update_user_meta( $uid, $meta_key, $value );
			}

			// Send an email for each new user
			$this->new_user_notifiaction( $uid );

			$message = 'Almost done! We\'ve sent a confirmation email to <strong>'. $user_email .'</strong>. Click the link in the email to confirm this address.';

			$returned_data = array(
				'status' 	=> 'success',
				'message' 	=> array( $message ),
			);

			$_POST['user_id'] = $uid;

			$headers[] = 'From: HeadReach <contact@headreach.com>';
			$headers[] = 'Content-Type: text/html; charset=UTF-8';

			$to = $user_email;
			
			$subject = 'Confirm your email — HeadReach';

			$token_link = get_option( 'home' ) . '?activation-token=%s';
			$message = 'Thank you for signing up with HeadReach! Please click on <a href="'. $token_link .'">this link</a> to confirm your email!';
			$message .= '<br /><br />';
			$message .= 'Alternatively, copy and paste the following link in your browser\'s web address:';
			$message .= '<br />';
			$message .= $token_link;

			EmailConfirmation::send($to, $subject, $message, $headers);

			die( json_encode( $returned_data ) );

		} else {

			$errors = $uid->errors;
			$errors_list = array();

			foreach ($errors as $err) {
				$errors_list[] = $err;
			}

			// Display an error status to the user
			$this->show_message( 'alert', $errors_list );
			
		}

	}


	# Update Password
	public function lost_password() {
	 
	    // check the nonce, if it fails the function will break
	    check_ajax_referer( 'ajax-password-nonce', 'security-pw' );

		global $wpdb, $wp_hasher;

		$username = trim($_POST['login']);

		if ( empty( $username ) ) {
			$this->show_message( 'alert', '<strong>ERROR</strong>: Enter an username or e-mail address.' );
		}

		$user_exists = false;

		if ( username_exists( $username ) ) { // First check by username
			$user_exists = true;
			$user = get_user_by('login', $username);
		} elseif ( email_exists($username) ) { // Then, by e-mail address
			$user_exists = true;
			$user = get_user_by_email($username);
		} else {
			$this->show_message( 'alert', 'Username or Email was not found, try again!' );
		}

		if ( !$user_exists ) {
			$this->show_message( 'alert', 'User doesn\'t exist!' );
		}

		$user_login = $user->user_login;
		$user_email = $user->user_email;

		// $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));

		// Generate something random for a key...
		$key = wp_generate_password(20, false);
		do_action('retrieve_password_key', $user_login, $key);

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );

		// Now insert the new md5 key into the db
		$wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));

		$password_page = get_permalink( get_option( 'ae_password_reset_pid' ) );

		$args = array(
			'action' 	=> 'rp',
			'key' 		=> $key,
			'login' 	=> rawurlencode($user_login),
		);

		// create email message
		$message = __('Someone has asked to reset the password for the following site and username.') . "\r\n\r\n";
		$message .= get_option('siteurl') . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= add_query_arg( $args, $password_page ) . "\r\n";

		// send email meassage
		if ( false == wp_mail($user_email, sprintf(__('[%s] Password Reset'), get_option('blogname')), $message) ) {
			$this->show_message( 'alert', 'The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...' );
		}

		$rp_path = parse_url( $password_page );
		$rp_path = $rp_path['path'];
		
		$value = sprintf( '%s:%s', wp_unslash( rawurlencode($user_login) ), wp_unslash( $key ) );
		setcookie( $this->rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );

		$this->show_message( 'alert', 'A message will be sent to your email address.' );
	}


	public function change_password() {

		// check the nonce, if it fails the function will break
		check_ajax_referer( 'ajax-password-nonce', 'security-npw' );

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
		}

		$password_page = get_permalink( get_option( 'ae_password_reset_pid' ) );

		$rp_path = parse_url( $password_page );
		$rp_path = $rp_path['path'];

		if ( isset( $_POST['cookie-value'] ) && !empty($_POST['cookie-value']) ) {
			
			list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_POST['cookie-value'] ), 2 );

			$rp_login = rawurldecode($rp_login);

			if ( username_exists( $rp_login ) ) { // First check by username
				$user_exists = true;
				$user = get_user_by('login', $rp_login);
			} elseif ( email_exists($rp_login) ) { // Then, by e-mail address
				$user_exists = true;
				$user = get_user_by_email($rp_login);
			}

		}

		if ( empty($user) ) {
			$this->show_message( 'alert', 'Not this time, bro!' );
		}

		// Verify the users password
		if ( empty($_POST['pass1']) || empty($_POST['pass2']) ) {
			$this->show_message( 'alert', 'Please enter a password!' );
		}

		if ( isset($_POST['pass1']) && isset($_POST['pass2']) ) {
			if ( strcmp($_POST['pass1'], $_POST['pass2']) != 0 ) {
				$this->show_message( 'alert', 'The passwords you have entered do not match.' );
			}
		}

		// Reset the password
		reset_password($user, $_POST['pass1']);

		// Remote Password reset
		$username = $user->data->user_login;

		$app_user_data = array(
			'loginname' 	=> $username,
			'username' 		=> $username,
			'newpassword' 	=> $_POST['pass1'],
		);

		$server = carbon_get_user_meta( $user->ID, 'ae_server' );

		$activationengine = AE_API_Handler::api( $server );
		$activationengine->updatePassword( $app_user_data );

		setcookie( $this->rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );

		$this->show_message( 'success', 'Your password has been successfully updated!' );
	}

	
	private function wp_login( $creds ) {

		// WP Login
		$user = wp_signon( $creds, false );

		if ( is_wp_error( $user ) ) {

			if ( $user->get_error_code() == 'invalid_username' || $user->get_error_code() == 'incorrect_password' ) {
				
				$this->show_message( 'alert', 'ERROR: Incorrect username or password.' );

			} else {

				$errors = $user->errors;
				$errors_list = array();

				foreach ($errors as $err) {
					$errors_list[] = $err;
				}

				$this->show_message( 'alert', $errors_list );

			}

		}

		// User has logged in successfully
		return $user->ID;
	}

	public function isNotCompanyEmail( $email ) {

		if ( $email == 'lexis.startup@gmail.com' ) {
			return false;
		}

		$domains = array(
			/* Default domains included */
			"aol.com", "att.net", "comcast.net", "facebook.com", "gmail.com", "gmx.com", "googlemail.com",
			"google.com", "hotmail.com", "hotmail.co.uk", "mac.com", "me.com", "mail.com", "msn.com",
			"live.com", "sbcglobal.net", "verizon.net", "yahoo.com",

			/* Other global domains */
			"email.com", "games.com" /* AOL */, "gmx.net", "hush.com", "hushmail.com", "icloud.com", "inbox.com",
			"lavabit.com", "love.com" /* AOL */, "outlook.com", "pobox.com", "rocketmail.com" /* Yahoo */,
			"safe-mail.net", "wow.com" /* AOL */, "ygm.com" /* AOL */, "ymail.com" /* Yahoo */, "zoho.com", "fastmail.fm",
			"yandex.com",

			/* United States ISP domains */
			"bellsouth.net", "charter.net", "comcast.net", "cox.net", "earthlink.net", "juno.com",

			/* British ISP domains */
			"btinternet.com", "virginmedia.com", "blueyonder.co.uk", "freeserve.co.uk", "live.co.uk",
			"ntlworld.com", "o2.co.uk", "orange.net", "sky.com", "talktalk.co.uk", "tiscali.co.uk",
			"virgin.net", "wanadoo.co.uk", "bt.com",

			/* Domains used in Asia */
			"sina.com", "qq.com", "naver.com", "hanmail.net", "daum.net", "nate.com", "yahoo.co.jp", "yahoo.co.kr", "yahoo.co.id", "yahoo.co.in", "yahoo.com.sg", "yahoo.com.ph",

			/* French ISP domains */
			"hotmail.fr", "live.fr", "laposte.net", "yahoo.fr", "wanadoo.fr", "orange.fr", "gmx.fr", "sfr.fr", "neuf.fr", "free.fr",

			/* German ISP domains */
			"gmx.de", "hotmail.de", "live.de", "online.de", "t-online.de" /* T-Mobile */, "web.de", "yahoo.de",

			/* Russian ISP domains */
			"mail.ru", "rambler.ru", "yandex.ru", "ya.ru", "list.ru",

			/* Belgian ISP domains */
			"hotmail.be", "live.be", "skynet.be", "voo.be", "tvcablenet.be", "telenet.be",

			/* Argentinian ISP domains */
			"hotmail.com.ar", "live.com.ar", "yahoo.com.ar", "fibertel.com.ar", "speedy.com.ar", "arnet.com.ar",

			/* Domains used in Mexico */
			"hotmail.com", "gmail.com", "yahoo.com.mx", "live.com.mx", "yahoo.com", "hotmail.es", "live.com", "hotmail.com.mx", "prodigy.net.mx", "msn.com",

			/* Domains used in Brazil */
			"yahoo.com.br", "hotmail.com.br", "outlook.com.br", "uol.com.br", "bol.com.br", "terra.com.br", "ig.com.br", "itelefonica.com.br", "r7.com", "zipmail.com.br", "globo.com", "globomail.com", "oi.com.br",

			"naij.com", "freenet.de", "yahoo.jp", "yahoo.cn", "yahoo.ca", "yahoo.it", "yahoo.nl", "yahoo.ru", "yahoo.es", "sharklasers.com", "yahoo.com.tw",
			"jourrapide.com", "byom.de", "yahoo-inc.com", "mailinator.com", "inbox.ru",

			"techie.com", "usa.com", "hmamail.com", "redseerconsulting.com", "aim.com", "redseerconsulting.com", "wktechinsights.com"
		);

		$pieces = explode('@', $email);

		// Email not valid
		if ( !isset($pieces[1]) ) {
			return true;
		}

		if ( in_array($pieces[1], $domains) ) {
			return true;
		}

		return false;
	}


	/**
	* Notify the blog admin of a new user, normally via email.
	*
	* @since 2.0
	*
	* @param int $user_id User ID
	* @param string $plaintext_pass Optional. The user's plaintext password
	*/
	public function new_user_notifiaction( $user_id ){
		
		$user = get_userdata( $user_id );

		$user_login = stripslashes($user->user_login);
		$user_email = stripslashes($user->user_email);

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

		// @wp_mail(get_option('admin_email'), sprintf(__('%s New User Registration'), $blogname), $message);
		@wp_mail('contact@headreach.com', sprintf(__('%s New User Registration'), $blogname), $message);

		// User notification
		// $message  = sprintf(__('Congratulations, %s!'), $user->data->display_name) . "\r\n\n";
		// $message .= esc_html( get_option( 'ae_new_user' ) );

		// wp_mail($user_email, sprintf(__('Welcome to [%s]'), $blogname), $message);

	}


	/*
		As an AJAX callback, trigger a php die()
	*/
	public function show_message( $status, $message ) {

		if ( !is_array($message) ) {
			$message = (array) $message;
		}

		die(json_encode(
				array(
					'status' => $status,
					'message' => $message
				)
			)
		);

	}

}