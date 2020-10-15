<?php

define('CRB_THEME_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

# Enqueue JS and CSS assets on the front-end
add_action('wp_enqueue_scripts', 'crb_wp_enqueue_scripts');
function crb_wp_enqueue_scripts() {

	$template_dir = get_template_directory_uri();

	# Enqueue jQuery
	wp_enqueue_script('jquery');

	# Enqueue Custom JS files
	# @crb_enqueue_script attributes -- id, location, dependencies, in_footer = false
	// crb_enqueue_script('what-input',	 			$template_dir . '/js/vendor/what-input.js', array('jquery'));
	crb_enqueue_script('foundation',	 			$template_dir . '/js/vendor/foundation.min.js', array('jquery'), true);
	crb_enqueue_script('intlTelInput-js',	 		$template_dir . '/js/iti/intlTelInput.min.js', array('jquery'), true);
	crb_enqueue_script('theme-functions',	 		$template_dir . '/js/functions.js', array('jquery'), true);

	# Enqueue Custom CSS files
	# @crb_enqueue_style attributes -- id, location, dependencies, media = all

	if ( is_page_template( 'templates/account-page.php' ) OR
		is_page_template( 'templates/complete-registration.php' ) OR
		is_singular( 'product' )
	) {
		crb_enqueue_style('foundation', $template_dir . '/css/foundation-app.css');
		crb_enqueue_style('app-styles', $template_dir . '/css/inner-app.css');
	} else if ( is_page_template( 'templates/ebook.php' ) ) {
		crb_enqueue_style('foundation', $template_dir . '/css/foundation-book.css');
		crb_enqueue_style('promo-site', $template_dir . '/css/promo-site.css');
	} else {
		crb_enqueue_style('foundation', $template_dir . '/css/foundation.min.css');
		crb_enqueue_style('web-styles', $template_dir . '/css/app.min.css');
	}

	crb_enqueue_style('iti-styles', $template_dir . '/css/intlTelInput.css');
	crb_enqueue_style('woo-styles', $template_dir . '/css/woo-styles.css');
	crb_enqueue_style('theme-styles', 	$template_dir . '/style.css');

	# Enqueue Comments JS file
	if (is_singular()) {
		wp_enqueue_script('comment-reply');
	}
}

# Enqueue JS and CSS assets on admin pages
add_action('admin_enqueue_scripts', 'crb_admin_enqueue_scripts');
function crb_admin_enqueue_scripts() {
	$template_dir = get_template_directory_uri();

	# Enqueue Scripts
	# @crb_enqueue_script attributes -- id, location, dependencies, in_footer = false
	# crb_enqueue_script('theme-admin-functions', $template_dir . '/js/admin-functions.js', array('jquery'));
	
	# Enqueue Styles
	# @crb_enqueue_style attributes -- id, location, dependencies, media = all
	crb_enqueue_style('theme-admin-styles', $template_dir . '/css/admin.css');
}

# Attach Custom Post Types and Custom Taxonomies
add_action('init', 'crb_attach_post_types_and_taxonomies');
function crb_attach_post_types_and_taxonomies() {
	# Attach Custom Post Types
	include_once(CRB_THEME_DIR . 'options/post-types.php');

	# Attach Custom Taxonomies
	include_once(CRB_THEME_DIR . 'options/taxonomies.php');
}

add_action('after_setup_theme', 'crb_setup_theme');

# To override theme setup process in a child theme, add your own crb_setup_theme() to your child theme's
# functions.php file.
if (!function_exists('crb_setup_theme')) {
	function crb_setup_theme() {

		# Declare Woocommerce Support
		add_theme_support( 'woocommerce' );

		# Make this theme available for translation.
		load_theme_textdomain( 'crb', get_template_directory() . '/languages' );

		# Common libraries
		include_once(CRB_THEME_DIR . 'lib/common.php');
		include_once(CRB_THEME_DIR . 'lib/carbon-fields/carbon-fields.php');
		include_once(CRB_THEME_DIR . 'lib/carbon-validator/carbon-validator.php');
		include_once(CRB_THEME_DIR . 'lib/admin-column-manager/carbon-admin-columns-manager.php');

		# Additional libraries and includes
		include_once(CRB_THEME_DIR . 'includes/comments.php');
		include_once(CRB_THEME_DIR . 'includes/gravity-forms.php');
		include_once(CRB_THEME_DIR . 'includes/title.php');
		include_once(CRB_THEME_DIR . 'includes/AE_Validator.php');

		# Classes
		include_once(CRB_THEME_DIR . 'includes/classes/users.class.php');
		include_once(CRB_THEME_DIR . 'includes/classes/my-account-subscription.class.php');
		include_once(CRB_THEME_DIR . 'includes/classes/confirmation.class.php');

		if( !function_exists('wpthumb') ) {
			include_once(CRB_THEME_DIR . 'includes/wpthumb/wpthumb.php');
		}

		# Theme supports
		add_theme_support('automatic-feed-links');
		add_theme_support('menus');
		add_theme_support('post-thumbnails');

		if ( function_exists( 'add_image_size' ) ) {
			add_image_size( 'thumb-70', 70, 55, true );
			add_image_size( 'thumb-326', 326, 168, true );
		}

		# Manually select Post Formats to be supported - http://codex.wordpress.org/Post_Formats
		// add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );

		# Register Theme Menu Locations
		register_nav_menus(array(
			'main-menu' 	=> __('Main Menu', 'crb'),
		));
		
		# Attach custom widgets
		// include_once(CRB_THEME_DIR . 'options/widgets.php');

		# Attach custom shortcodes
		include_once(CRB_THEME_DIR . 'options/shortcodes.php');
		
		# Add Actions
		add_action('widgets_init', 'crb_widgets_init');
		add_action('wp_head', 'crb_ajax_url');
		add_action('wp_head', 'crb_ebook_scripts');

		add_action('carbon_register_fields', 'crb_attach_theme_options');

		# Add Filters
		$session_name = session_name( 'headreach_auth' );
		session_set_cookie_params(0, '/', 'headreach.com');
		
		ini_set('session.gc_maxlifetime', 60 * 60 * 48);

		if ( session_id() == '' )  {
			session_start();
		}


		define( 'ACTIVECAMPAIGN_URL', '' );
		define( 'ACTIVECAMPAIGN_API_KEY', '' );
		require_once(dirname(__FILE__) . '/includes/activecampaign-api-php/ActiveCampaign.class.php');

	}
}

# Register Sidebars
# Note: In a child theme with custom crb_setup_theme() this function is not hooked to widgets_init
function crb_widgets_init() {
	$sidebar_options = array_merge(crb_get_default_sidebar_options(), array(
		'name' => 'Default Sidebar',
		'id'   => 'default-sidebar',
	));
	
	register_sidebar($sidebar_options);
}

# Sidebar Options
function crb_get_default_sidebar_options() {
	return array(
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget'  => '</li>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>',
	);
}

function crb_attach_theme_options() {
	# Attach fields
	include_once(CRB_THEME_DIR . 'options/theme-options.php');
	include_once(CRB_THEME_DIR . 'options/custom-fields.php');
}

// Remove each style one by one
add_filter( 'woocommerce_enqueue_styles', 'crb_dequeue_woo_styles' );
function crb_dequeue_woo_styles( $enqueue_styles ) {
	unset( $enqueue_styles['woocommerce-general'] );	// Remove the gloss
	unset( $enqueue_styles['woocommerce-layout'] );		// Remove the layout
	// unset( $enqueue_styles['woocommerce-smallscreen'] );	// Remove the smallscreen optimisation
	return $enqueue_styles;
}

# Get all categories and return an array with their ids
function crb_terms_array( $tax ) {

	$terms 		= get_terms( $tax, 'order=ASC&hide_empty=0' );
	$term_ids 	= array('N/A');

	foreach ($terms as $term) {
		$term_ids[$term->term_id] = $term->name;		
	}

	return $term_ids;
}

# Return a basic yes/no array
function crb_switcher() {
	return array(
		'no' 	=> 'No',
		'yes' 	=> 'Yes'
	);
}

# Return an array with post IDs
function crb_get_cpt_ids($post_type) {

	$posts 		= get_posts('post_type='. $post_type .'&posts_per_page=-1');
	$post_ids 	= array('N/A');

	foreach ($posts as $p) {
		$post_ids[$p->ID] = $p->post_title;
	}

	return $post_ids;
}

add_filter('excerpt_more', 'crb_excerpt_more');
function crb_excerpt_more( $more ) {
	return '...';
}

function crb_htmlize($text, $tag = 'span') {

	if ( empty($text) ) {
		return;
	}

	$htmlized = preg_replace('~\*([^*]*)\*~', '<' . $tag . '>$1</' . $tag . '>', $text);

	return $htmlized;
}

add_filter( 'crb_get_title', 'crb_alt_page_title' );
function crb_alt_page_title( $title ) {
	
	$alt_title = crb_get_meta( '_crb_page_alt_title' );

	if ( empty($alt_title) ) {
		return $title;
	}

	return crb_htmlize( $alt_title, 'strong' );
}

# Add specific CSS class by filter
add_filter( 'body_class', 'crb_body_classes' );
function crb_body_classes( $classes ) {

	// Assign the possible page templates and their corresponding classes
	$data = array(
		'default' 				=> 'default-page',
		'templates/pricing.php' => 'pricing',
		'templates/login.php' 	=> 'sign-up-section',
		'templates/signup.php' 	=> 'sign-up-section',
		'templates/account-page.php' => 'bg-gray',
		'templates/complete-registration.php' => 'bg-gray',
	);

	foreach ($data as $template => $class) {
		$current = crb_get_meta( '_wp_page_template' );

		if ( $current == $template ) {
			$classes[] = $class;
		}
	}

	if ( is_singular( 'product' ) ) {
		$classes[] = 'bg-gray';
	}

	if ( crb_get_meta( '_crb_add_account_styles' ) ) {
		$classes[] = 'flex-account-page';
	}

	// return the $classes array
	return $classes;
}

add_filter( 'woocommerce_before_customer_login_form', 'crb_wrap_start' );
add_filter( 'woocommerce_after_customer_login_form', 'crb_wrap_end' );

function crb_wrap_start() {
	?>
	<div class="woo-wrapper">
	<?php
}

function crb_wrap_end() {
	?>
	</div>
	<?php
}

add_filter( 'woocommerce_account_menu_items', 'crb_woo_side_menu' );
function crb_woo_side_menu( $items ) {
	$items_new = array();

	foreach ($items as $key => $label) {
		if ( $label == 'Orders' ) {
			$label = 'Billing';
		}

		$items_new[$key] = $label;
	}

	$items_new['subscription'] = 'Subscription';

	unset($items_new['downloads']);
	unset($items_new['customer-logout']);
	unset($items_new['orders']);
	unset($items_new['edit-address']);

	return $items_new;
}

// add_filter( 'add_to_cart_redirect', 'crb_redirect_to_checkout' );
add_filter( 'woocommerce_add_to_cart_redirect', 'crb_redirect_to_checkout' );
function crb_redirect_to_checkout() {
	return WC()->cart->get_checkout_url();
}

add_action( 'template_redirect', 'crb_redirect_woo_pages' );
function crb_redirect_woo_pages() {

	if ( get_the_ID() == 7 ) {
		// Emtpy the cart
		WC()->cart->empty_cart();
	}

	if ( current_user_can( 'administrator' ) ) {
		return false;
	}

	$cart_obj = WC()->cart->get_cart();

	if ( is_shop() || is_product_category() || is_product_tag() || is_cart() ) {
		
		if ( empty($cart_obj) ) {
			wp_redirect( get_option( 'home' ) . '/my-account/subscription/' );
		} else {
			wp_redirect( WC()->cart->get_checkout_url() );
		}

		exit;
	}

	// Check if a product is already added to the cart
	$cart_contents_num = WC()->cart->get_cart_contents_count();
	if ( is_singular( 'product' ) && $cart_contents_num > 0 ) {
		wp_redirect( WC()->cart->get_checkout_url() );
		exit;
	}

}

// Validate the cart
add_filter( 'woocommerce_add_to_cart_validation', 'crb_check_if_cart_has_product', 20, 4 );

function crb_check_if_cart_has_product( $passed = true, $product_id, $quantity, $variation_id = '' ) {  

	$cart_obj = WC()->cart->get_cart();

	if( !empty($cart_obj) && $passed ){
		foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
			$_product = $values['data'];

			if ( $product_id == $_product->id ) {
				wc_add_notice( 'The product is already in cart', 'error' );
				$passed = false;
			}
		}
	}

    return $passed;
}

// woo commerce

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {

    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_phone']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['order']['order_comments']);

    $fields['billing']['billing_email']['class'] = array( 'form-row-wide' );

    return $fields;
}

// add_action( 'woocommerce_thankyou', 'crb_custom_tracking' );
function crb_custom_tracking( $order_id ) {

	// Lets grab the order
	// $order = new WC_Order( $order_id );
	
	?>
	
	<!-- Boostinsider Conversion Code For WooGuru - Unlimited Support for WooCommerce --><script>(function() {
	  var _biq = window._biq || (window._biq = []);
	  if (!_biq.loaded) {
	    var bids = document.createElement('script');
	    bids.async = true;
	    bids.src = 'https://www.boostinsider.com/js/bids.js';
	    var s = document.getElementsByTagName('script')[0];
	    s.parentNode.insertBefore(bids, s);
	    _biq.loaded = true;
	  }
	
	_biq.push(['addCampaignId', 'f7fdd4ea6c687d4c6b573ad8d195484e']);
	})();
	window._biq = window._biq || [];
	window._biq.push(['track', 'f7fdd4ea6c687d4c6b573ad8d195484e', {'value':'0.00','currency':'USD'}]);
	</script><noscript><img height="1" width="1" alt="" style="display:none" src="https://www.boostinsider.com/_btp.gif?cid=f7fdd4ea6c687d4c6b573ad8d195484e&cd[value]=0.00&cd[currency]=USD&noscript=1" /></noscript>
	
	<?php
}

add_action( 'template_redirect', 'crb_ssl_redirect' );
function crb_ssl_redirect() {

	// if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
	// 	header('Location: http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's':'').'://' . substr($_SERVER['HTTP_HOST'], 4).$_SERVER['REQUEST_URI']);
	// 	exit;
	// }

	if ( !is_ssl() ) {
		wp_safe_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301 );
    	exit();
	}
}

# The main Ajax URL for our team
function crb_ajax_url() {
	?>

	<script type="text/javascript">
		var ajaxUrl = "<?php echo admin_url( 'admin-ajax.php' ) ?>";
	</script>

	<?php
}

add_action('wp_login', 'hr_login_action', 10, 2);
function hr_login_action( $user_login, $user ) {
	$user_id = $user->ID;
	// crb_add_products_main( $user_id );
	set_user_session( $user_id );
}

add_action( 'switch_to_user', 'hr_switch_users', 99, 1 );
function hr_switch_users( $user_id ) {
	unset($_SESSION['current-user']);
	set_user_session( $user_id );
}

function set_user_session( $user_id ) {

	$is_active = carbon_get_user_meta( $user_id, 'crb_user_activated' );

	if ( !$is_active ) {
		return false;
	}

	$user_info = get_userdata( $user_id );
	$g_image = get_avatar_url( $user_id );

	$has_sub = false;
	if ( function_exists('wcs_user_has_subscription') ) {
		$has_sub = wcs_user_has_subscription( '', '', 'active' );
	}
	
	$_SESSION['current-user'] = array(
		'ID' => $user_id,
		'user_login' => $user_info->data->user_login,
		'user_first_name' => $user_info->first_name,
		'avatar' => $g_image,
		'role' => $user_info->roles[0],
		'has_sub' => ( $user_id == 1 ? 1 : $has_sub ),
	);
}


add_action('wp_logout', 'hr_logout_action');
// add_action('delete_user', 'hr_logout_action');
function hr_logout_action() {

	// Initialize the session.
	// If you are using session_name("something"), don't forget it now!
	session_start();

	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if ( ini_get( 'session.use_cookies' ) ) {
	    $params = session_get_cookie_params();
	    setcookie(session_name( 'headreach_auth' ), '', time() - 42000,
	        $params['path'], $params['domain'],
	        $params['secure'], $params['httponly']
	    );
	}

	// Finally, destroy the session.
	session_destroy();
	
}

add_action( 'user_register', 'hr_register_action', 10, 1 );
// add_action( 'template_redirect', 'hr_register_action', 10 );
function hr_register_action( $user_id ) {
	global $wpdb;

	$wpdb->insert('wp_ext_credits', array(
		'wp_user_id' => $user_id,
		'credits' => get_option( 'crb_initial_credits' ),
	));

	$last_credit_id = $wpdb->insert_id;

	$wpdb->insert('wp_ext_credits_log', array(
		'credit_entry_id' => $last_credit_id,
		'type' => 'registration_promotion',
		'credit_amount_earned' => get_option( 'crb_initial_credits' ),
	));

}

// add_action( 'template_redirect', 'hr_custom_process_order' );
// add_action('woocommerce_subscription_payment_complete', 'hr_custom_process_order', 10, 1);
// function hr_custom_process_order() {

add_action('woocommerce_payment_complete', 'hr_custom_process_order', 10, 1);
function hr_custom_process_order( $order_id ) {
	global $wpdb;

	$order = new WC_Order( $order_id );
	$user_id = (int) $order->user_id;
	$user_info = get_userdata( $user_id );

	// Get the credit entry
	$credit_entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ext_credits WHERE wp_user_id = $user_id", ARRAY_A ) );
	if ( empty($credit_entry) ) {
		return $order_id;
	}

	$credit_entry_id = $credit_entry->id;

	$items = $order->get_items();

	if ( empty($items) ) {
		return $order_id;
	}

	foreach ($items as $item) {
		$subscription = $item;
		break;
	}

	$points_to_insert = 0;

	$sbs_name = $subscription['name'];
	switch ($sbs_name) {
		case 'HeadReach Subscription':
			$points_to_insert = 500;

			if ( $user_info->data->user_email == 'fraser@platonik.co.uk' ) {
				$points_to_insert = 600;
			}

			break;
		
		case 'HeadReach Subscription - $39':
			$points_to_insert = 250;
			break;

		case 'HeadReach Subscription - $19':
			$points_to_insert = 100;
			break;

		case 'HeadReach Subscription - $180':
			$points_to_insert = 1500;
			break;
	}

	$wpdb->update( 
		'wp_ext_credits', 
		array( 'credits' => $points_to_insert ),
		array( 'wp_user_id' => $user_id ),
		array( '%d' ),
		array( '%d' )
	);

	$wpdb->insert('wp_ext_credits_log', array(
		'credit_entry_id' => $credit_entry_id,
		'type' => 'purchase',
		'credit_amount_earned' => $points_to_insert,
	));

	return $order_id;
}

add_action('woocommerce_payment_complete', 'hr_update_ac_subscriber', 20, 1);
function hr_update_ac_subscriber( $order_id ) {

	$order = new WC_Order( $order_id );
	$user_id = (int) $order->user_id;
	$user_info = get_userdata( $user_id );

	$subscriber_id = get_user_meta( $user_id, '_ac_subscriber_id', true );

	if ( empty($subscriber_id) ) {
		return $order_id;
	}

	$ac = getActiveCampaign();

	$method = 'contact/edit';
	$params = array(
		'id' => $subscriber_id,
		'email' => $user_info->user_email,
		'p[7]' => 7,
		'tags' => 'paying user',
		'status[7]' => 1,
		'instantresponders[7]' => 1,
	);

	$ac->api( $method, $params );

	return $order_id;
}

add_action('woocommerce_order_status_cancelled', 'hr_update_ac_unsubscribe', 10, 1);
function hr_update_ac_unsubscribe( $order_id ) {

	$order = new WC_Order( $order_id );

	if ( $order->post_status != 'wc-completed' ) {
		return $order_id;
	}

	$user_id = (int) $order->user_id;
	$user_info = get_userdata( $user_id );

	$subscriber_id = get_user_meta( $user_id, '_ac_subscriber_id', true );

	if ( empty($subscriber_id) ) {
		return $order_id;
	}

	$ac = getActiveCampaign();

	$ac->api('contact/tag_remove', array(
		'id' => $subscriber_id,
		'email' => $user_info->user_email,
		'tags' => 'paying user',
	));

	$ac->api('contact/edit', array(
		'id' => $subscriber_id,
		'email' => $user_info->user_email,
		'p[7]' => 7,
		'tags' => 'unsubscribed',
		'status[7]' => 1,
		'instantresponders[7]' => 1,
	));

	return $order_id;
}

function getCredits( $wp_user_id = false ) {
	global $wpdb;

	if ( empty($wp_user_id) ) {
		$wp_user_id = get_current_user_id();
	}

	$sql = "SELECT wp_ext_credits.*,
			wp_ext_credits_log.credit_amount_earned,
			wp_ext_credits_log.type
			FROM wp_ext_credits
			INNER JOIN wp_ext_credits_log
			ON wp_ext_credits_log.credit_entry_id = wp_ext_credits.id
			WHERE wp_ext_credits.wp_user_id = $wp_user_id
			ORDER BY wp_ext_credits_log.id DESC
			";

	$credits = $wpdb->get_row( $sql, 'ARRAY_A' );

	if ( empty($credits) ) {
		return array(
			'left' => 0,
			'used' => 0,
			'total' => 0,
			'percentage_used' => 0,
			'type' => '',
		);
	}

	$total = $credits['credit_amount_earned'];
	$current = $credits['credits'];

	$percentage_used = (($total - $current )/$total) * 100;

	return array(
		'left' => $current,
		'used' => $total - $current,
		'total' => $total,
		'percentage_used' => number_format( $percentage_used, 2 ),
		'type' => $credits['type'],
	);
}

function crb_add_products_main( $user_id ) {

	// Emtpy the cart
	WC()->cart->empty_cart();

	$products = carbon_get_theme_option( 'crb_user_products' );

	if ( empty($products) ) {
		return false;
	}

	foreach ($products as $product_id) {
		$found = false;
		//check if product already in cart
		if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				$_product = $values['data'];

				if ( $_product->id == $product_id ) {
					$found = true;
				}
			}

			// if product not found, add it
			if ( ! $found ) {
				WC()->cart->add_to_cart( $product_id );
			}
		} else {
			// if no products in cart, add it
			WC()->cart->add_to_cart( $product_id );
		}
	}

	return true;
}

function crb_ebook_scripts() {

	if ( !is_page_template( 'templates/ebook.php' ) ) {
		return false;
	}

	?>

	<script type="text/javascript">(function(d, t){
	if(window.location.hash!='#gleam'&&(''+document.cookie).match(/(^|;)\s*GleamgNGQS=X($|;)/)){return;}
	var g = d.createElement(t), s = d.getElementsByTagName(t)[0];
	g.src = "https://js.gleam.io/gNGQS/ol.js"; s.parentNode.insertBefore(g, s);
	}(document, "script"));</script>
		  	
	<script src="https://js.gleam.io/oi-NexF4217.js" async="async"></script>
	
	<!-- Analytics -->
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
	  ga('create', 'UA-73177323-1', 'auto');
	  ga('send', 'pageview');
	
	</script>

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@HeadReachApp">
	<meta name="twitter:creator" content="@HeadReachApp">
	<meta name="twitter:title" content="Get The Champion's Guide to Outreach Marketing FREE eBook + 4 Bonuses">
	<meta name="twitter:description" content="150 pages of actionable tips, tactics and case studies on outreach marketing and bloggeroutreach.">
	<meta name="twitter:image" content="<?php bloginfo( 'stylesheet_directory'); ?>/images/ebook/ebook.jpg">

	<?php
}

add_action( 'admin_init', 'hr_app_connection' );
function hr_app_connection() {
	global $app_db;
	$app_db = new wpdb('headreach', 'NoPasswd@1', 'headreach_db_dev', 'localhost');
}

add_action( 'template_redirect', 'hr_check_user_credits' );
function hr_check_user_credits() {

	if ( !isset($_GET['check-credits']) OR !is_numeric($_GET['check-credits']) ) {
		return false;
	}

	// Not the safest solution
	$wp_user_id = $_GET['check-credits'];

	$credits = getCredits( $wp_user_id );
	
	if ( empty($credits) ) {
		return false;
	}

	if ( $credits['type'] != 'registration_promotion' ) {
		return false;
	}

	$credits_left = $credits['left'];

	$tag = '';

	if ( $credits_left == 3 ) {
		$tag = 'three_credits_left';
	} else if ( $credits_left == 9 ) {
		$tag = 'one_credit_used';
	} else if ( $credits_left == 0 ) {
		$tag = 'no_credits_left';
	}

	if ( empty($tag) ) {
		return false;
	}

	$user_info = get_userdata( $wp_user_id );
	$subscriber_id = get_user_meta( $wp_user_id, '_ac_subscriber_id', true );

	if ( empty($subscriber_id) ) {
		return false;
	}

	$ac = getActiveCampaign();

	$method = 'contact/edit';
	$params = array(
		'id' => $subscriber_id,
		'email' => $user_info->user_email,
		'p[7]' => 7,
		'status[7]' => 1,
		'instantresponders[7]' => 1,
		'tags' => $tag,
	);

	$ac->api( $method, $params );

	return true;
}

add_action( 'template_redirect', 'test_ips' );
function test_ips() {

	if ( !isset($_GET['test_ips']) ) {
		return false;
	}

	$headers[] = 'From: HeadReach <contact@headreach.com>';
	$headers[] = 'Content-Type: text/html; charset=UTF-8';

	$to = 'dogostz@gmail.com';
	$subject = 'Confirm';
	// The unique token can be inserted in the message with %s
	$message = 'Thank you. Please <a href="'. get_option( 'home' ) .'?activation-token=%s">confirm</a> to continue';

	EmailConfirmation::send($to, $subject, $message, $headers);

	die( 'done' );

}

add_action( 'template_redirect', 'hr_validate_token' );
function hr_validate_token() {

	if ( !isset($_GET['activation-token']) ) {
		return false;
	}

	$user_info = EmailConfirmation::check( $_GET['activation-token'] );

	if ( empty($user_info) ) {
		return false;
	}

	if ( !isset($user_info['user_id']) OR empty($user_info['user_id']) ) {
		return false;
	}

	$user_id = $user_info['user_id'];

	update_user_meta($user_id, '_crb_user_activated', 'yes');

	// Sign-in the user
	$creds = array(
		'user_login'    => $user_info['email_address'],
		'user_password' => $user_info['user_password'],
		'rememember'    => true
	);

	wp_signon( $creds );

	wp_redirect( get_option( 'crb_register_redirect_url' ) );

	return true;
}

add_action( 'template_redirect', 'migrate_users' );
function migrate_users() {

	if ( !isset($_GET['migrate-users']) ) {
		return false;
	}

	$users = get_users();

	foreach ($users as $user) {
		$user_id = $user->ID;
		update_user_meta($user_id, '_crb_user_activated', 'yes');
	}

	die( 'done' );
}


function ip_registered( $ip ) {
	global $wpdb;

	// Get the credit entry
	$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `wp_usermeta` WHERE `meta_key` LIKE 'signup_ip' AND `meta_value` LIKE '$ip'", ARRAY_A ) );

	if ( $entry ) {
		return true;
	}

	return false;
}

add_action( 'woocommerce_checkout_after_order_review', 'hr_checkout_extra_msg' );
function hr_checkout_extra_msg() {
	?>

	<div class="pay-with-card-message">
		<p>You can use credit or debit card, too. <a target="_blank" href="http://help.headreach.com/article/wR9snOmx0z-using-your-debit-or-credit-card-to-subscribe-to-head-reach">Learn how</a></p>
	</div>

	<?php
}

function getActiveCampaign() {
	$api_url = 'https://headreach.api-us1.com';
	$api_key = 'd22e414003e03b037b5cad6f48a21375c9b1d87ee06477d6dedbce5233cb20f491291212';

	$ac = new ActiveCampaignWordPress( $api_url, $api_key );

	return $ac;
}

/**
 * Optimize WooCommerce Scripts
 * Remove WooCommerce Generator tag, styles, and scripts from non WooCommerce pages.
 */
add_action( 'wp_enqueue_scripts', 'hr_manage_woocommerce_styles', 99 );
function hr_manage_woocommerce_styles() {
	//remove generator meta tag
	remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );

	// first check that woo exists to prevent fatal errors
	if ( function_exists( 'is_woocommerce' ) ) {
		//dequeue scripts and styles
		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
			wp_dequeue_style( 'woocommerce_frontend_styles' );
			wp_dequeue_style( 'woocommerce_fancybox_styles' );
			wp_dequeue_style( 'woocommerce_chosen_styles' );
			wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
			wp_dequeue_script( 'wc_price_slider' );
			wp_dequeue_script( 'wc-single-product' );
			wp_dequeue_script( 'wc-add-to-cart' );
			wp_dequeue_script( 'wc-cart-fragments' );
			wp_dequeue_script( 'wc-checkout' );
			wp_dequeue_script( 'wc-add-to-cart-variation' );
			wp_dequeue_script( 'wc-single-product' );
			wp_dequeue_script( 'wc-cart' );
			wp_dequeue_script( 'wc-chosen' );
			wp_dequeue_script( 'woocommerce' );
			wp_dequeue_script( 'prettyPhoto' );
			wp_dequeue_script( 'prettyPhoto-init' );
			wp_dequeue_script( 'jquery-blockui' );
			wp_dequeue_script( 'jquery-placeholder' );
			wp_dequeue_script( 'fancybox' );
			wp_dequeue_script( 'jqueryui' );
		}
	}

}

// Remove Emoji Icons
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// add_action('pre_get_posts', 'crb_sort_subscribers');
function crb_sort_subscribers( $query ) {

	if ( 
		is_admin() 
		// && $query->is_main_query() 
		// && get_query_var( 'post_type' ) === 'page'
		&& !empty( $_GET['orderby'] )
		&& $_GET['orderby'] === 'has_subscription'
	) {
		// $query->set('orderby', 'meta_value_num');
		$query->set('meta_key', 'paying_customer');
		$query->set('meta_value', '1');
	}

	return $query;
}

add_action( 'wp_ajax_user_cancellation_handler', 'hr_user_cancellation_handler' );
function hr_user_cancellation_handler() {

	if ( !isset($_POST['data']) ) {
		return false;
	}

	$data = $_POST['data'];

	$map = array(
		'has_finished' => 'I finished the work I needed to do',
		'another_tool' => 'I switched to another tool',
		'low_data_quality' => 'The data quality is too low',
		'missing_feature' => 'A feature is missing',
		'too_expensive' => 'HeadReach is too expensive',
	);

	$data = explode('&', $data);

	$labels = '';
	$explanation = '';

	foreach ($data as $entry) {
		$pieces = explode('=', $entry);
		$key = $pieces[0];

		if ( !empty($pieces[1]) AND $key != 'explanation' ) {
			$label = $map[$key];
			$labels .= $label . '; ';
		} else if ( $pieces[1] AND $key == 'explanation' ) {
			$explanation = $pieces[1];
		}
	}

	$user_id = get_current_user_id();

	if ( $labels ) {
		update_user_meta( $user_id, '_crb_cancellation_reason', $labels );
	}

	if ( $explanation ) {
		$text = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($explanation)); 
		$text = html_entity_decode($text,null,'UTF-8');
		$text = str_replace('\\', '', $text);
		update_user_meta( $user_id, '_crb_cancellation_reason_description', $text );
	}

	$user = get_userdata( $user_id );

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New Subscription cancellation %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

	// @wp_mail(get_option('admin_email'), sprintf(__('%s New User Registration'), $blogname), $message);
	@wp_mail('contact@headreach.com', sprintf(__('%s User Subscription Canceled'), $blogname), $message);

	return true;
}

add_action( 'template_redirect', 'hr_renew_user_session' );
function hr_renew_user_session() {

	if ( !isset($_GET['renew-session']) ) {
		return false;
	}

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		set_user_session( $user_id );

		wp_redirect( get_option( 'crb_register_redirect_url' ) );
		
		return true;
	}

}

add_action( 'admin_enqueue_scripts', 'load_admin_styles' );
function load_admin_styles() {
	wp_enqueue_style( 'admin_css_foo', get_template_directory_uri() . '/admin-styles.css', false, '1.0.0' );
}