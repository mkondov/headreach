<?php
/**
 * Plugin Name: Subscription Reports for WooCommerce
 * Plugin URI: http://www.storeapps.org/product/woocommerce-subscription-reports/
 * Description: Analytics & Insights for WooCommerce Subscriptions
 * Version: 1.6
 * Author: Store Apps
 * Author URI: http://www.storeapps.org/
 * Requires at least: 3.3
 * Tested up to: 4.6.1
 * Text Domain: woocommerce-subscription-reports
 * Domain Path: /languages/
 * Copyright (c) 2013, 2014, 2015, 2016 Store Apps
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Hooks
register_activation_hook ( __FILE__, 'wsr_activate' );
register_deactivation_hook ( __FILE__, 'wsr_deactivate' );


/**
 * Registers a plugin function to be run when the plugin is activated.
 */
function wsr_activate() {
	// Redirect to WSR
    if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) {
        set_transient( '_wsr_activation_redirect', 1, 30 );
    }
}

/**
 * Registers a plugin function to be run when the plugin is deactivated.
 */
function wsr_deactivate() {
	global $wpdb, $blog_id;
	if ( is_multisite() ) {
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}", 0 );
	} else {
		$blog_ids = array( $blog_id );
	}
	foreach ( $blog_ids as $blog_id ) {
		$wpdb_obj = clone $wpdb;
		$wpdb->blogid = $blog_id;
		$wpdb->set_prefix( $wpdb->base_prefix );

		$table_name = "{$wpdb->prefix}wsr_orders";
		if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
			$wpdb->query( "DROP TABLE {$wpdb->prefix}wsr_orders" );
		}
		
		$wpdb = clone $wpdb_obj;
	}
}

$active_plugins = (array) get_option('active_plugins', array());

if (is_multisite()) {
	$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
}

if ( ( in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) ) && 
	( in_array('woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins) || array_key_exists('woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins) ) ) {

	if ( ! class_exists( 'WC_Subscription_Reporting' ) ) {

		/**
		 * class WC_Subscription_Reporting
		 * 
		 * @return object of WC_Subscription_Reporting having all functionality of Subscription Reporting
		 * 
		 */
		class WC_Subscription_Reporting {

			public $version = '';

			public static $wsr_nonce = '';
			public static $wsr_prefix = 'woocommerce-subscription-reports';
			
			/**
			 * Constructor
			 */
			public function __construct() {

				include_once 'classes/class-subscription-reports-base.php';

				add_action ( 'woocommerce_checkout_order_processed', array( &$this,'define_nonce') ); // for handling guest checkouts

				add_action( 'init', array($this, 'define_nonce') ); //defining nonce

				$this -> define_constants(); //defining constants

				add_action( 'init', array($this, 'localize') );

				if (is_admin()) {

					global $wpdb;

					if ( ! function_exists( 'get_plugins' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}

					$plugin_info 	= get_plugins();
					$this->version = $plugin_info[WSR_PLUGIN_FILE]['Version'];

					add_filter( 'woocommerce_admin_reports', array( &$this, 'add_tab'), 10, 1 );
					add_action('wp_ajax_get_sub_stats', array( &$this, 'request_handler') );

					add_action( 'admin_enqueue_scripts', array( &$this,'enqueue_admin_scripts') );
					add_action( 'admin_enqueue_scripts', array( &$this,'enqueue_admin_styles') );

					add_action( 'admin_footer', array( $this, 'add_plugin_style_script' ) );
					add_filter( 'plugin_action_links_'.WSR_PLUGIN_FILE, array( $this, 'plugin_action_links' ) );

					if ( get_transient( '_wsr_activation_redirect' ) ) {
						// Delete the redirect transient
						delete_transient( '_wsr_activation_redirect' );
	    				wp_redirect( admin_url('admin.php?page=wc-reports&tab=subscriptions') );
	    			}

	    			//code for updating the db - for MRR related fix
	    			$current_wsr_version    = get_option( 'sa_wsr_db_version', false );

	    			if( empty($current_wsr_version) ) {
	    				$table_name = "{$wpdb->prefix}wsr_orders";
						if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
							$wpdb->query( "DROP TABLE {$wpdb->prefix}wsr_orders" );
						}

						update_option( 'sa_wsr_db_version', '1.6' );
	    			}

	    			//filters for handling quick_help_widget
					add_filter( 'sa_active_plugins_for_quick_help', array( $this, 'quick_help_widget' ), 10, 2 );

					// add_action( 'subscriptions_put_on_hold_for_order', 	array( &$this,'add_order') );
				}


				add_action( 'woocommerce_order_actions_start', 'Subscription_Reporting_Base::add_order',10,1 );
				add_action( 'woocommerce_order_status_changed', 	'Subscription_Reporting_Base::add_order',11,1 );
				add_action ( 'woocommerce_order_refunded' , 'Subscription_Reporting_Base::add_order',10,2 ); // added for handling manual refunds

				add_action ( 'woocommerce_subscription_status_changed' , 'Subscription_Reporting_Base::update_sub_status',10,3 ); // for handling subscription status change
				add_action ( 'woocommerce_process_shop_subscription_meta' , 'Subscription_Reporting_Base::update_sub_meta',10,2 ); // for handling subscription status change

				add_action( 'deleted_post', 'Subscription_Reporting_Base::delete_sub' );
				add_action( 'trashed_post', 'Subscription_Reporting_Base::trash_sub' );
				add_action( 'untrashed_post', 'Subscription_Reporting_Base::untrash_sub' );

				add_filter ( 'wcs_renewal_order_created' , 'Subscription_Reporting_Base::add_renewal_order',10,2 ); // added for handling renewals
			}

			public function define_nonce() {
				self::$wsr_nonce = wp_create_nonce( 'wsr-security' );
			}

			public function define_constants() {


				define ( 'WSR_DOMAIN', 'woocommerce-subscription-reports' );
				define ( 'WSR_NUMBER_FORMAT', get_option( 'wsr_number_format' ));
				$plugin = plugin_basename ( __FILE__ );
				define ( 'WSR_PLUGIN_FILE', $plugin );
				define ( 'WSR_PLUGIN_DIRNAME', dirname($plugin) );

				if (is_admin()) {
				    define ( 'WSR_IMG_UP', 'fa fa-arrow-up icon_cumm_indicator');
				    define ( 'WSR_IMG_DOWN', 'fa fa-arrow-down icon_cumm_indicator');
				    define ( 'WSR_CURRENCY_SYMBOL', get_woocommerce_currency_symbol());
					define ( 'WSR_CURRENCY_POS' , get_woocommerce_price_format());
					define ( 'WSR_DECIMAL_PLACES', get_option( 'woocommerce_price_num_decimals' ));
					define ( 'WSR_THOUSAND_SEP', get_option( 'woocommerce_price_thousand_sep' ));
					define ( 'WSR_DECIMAL_SEP', get_option( 'woocommerce_price_decimal_sep' ));
				}

			}

			public function enqueue_admin_scripts() {
				if ( !wp_script_is( 'jquery' ) ) {
		            wp_enqueue_script( 'jquery' );
		        }

		        wp_enqueue_script( 'wsr_d3_js', plugins_url ( '/assets/js/d3.v3.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ), $this->version );
		        wp_enqueue_script( 'wsr_d3_tip_js', plugins_url ( '/assets/js/d3.tip.v0.6.3.js', __FILE__ ), array( 'wsr_d3_js' ), $this->version );
		        wp_enqueue_script( 'wsr_functions_js', plugins_url ( '/assets/js/admin-functions.js', __FILE__ ), array( 'wsr_d3_tip_js' ), $this->version );
			}

			public function enqueue_admin_styles() {

				if( !empty($_GET) && (!empty($_GET['page']) && $_GET['page'] == 'wc-reports') && ( empty($_GET['tab']) || ( !empty($_GET['tab']) && $_GET['tab'] == 'subscriptions' )) ) {
					$deps = '';

					wp_register_style ( 'font_awesome', plugins_url ( '/assets/css/font-awesome/css/font-awesome.min.css', __FILE__ ), $deps, $this->version );
					wp_register_style ( 'wsr_main', plugins_url ( '/assets/css/subscription-report.css', __FILE__ ), array('font_awesome'), $this->version );
					wp_enqueue_style ( 'wsr_main' );	
				}
			}

			public function add_plugin_style_script() {

				if (!wp_script_is('thickbox')) {
	            	if (!function_exists('add_thickbox')) {
	                	require_once ABSPATH . 'wp-includes/general-template.php';
	            	}
	            	add_thickbox();
	        	}

				?>
            		<script type="text/javascript">
	                	jQuery(function(){
							jQuery(document).ready(function(){
								jQuery('tr[id="subscription-reports-for-woocommerce"]').find( 'div.plugin-version-author-uri' ).addClass( '<?php echo self::$wsr_prefix;?>_social_links' );
								jQuery('tr[data-slug="subscription-reports-for-woocommerce"]').find( 'div.plugin-version-author-uri' ).addClass( '<?php echo self::$wsr_prefix;?>_social_links' );
							});
						});
					</script>
				<?php
			}

			public function plugin_action_links($links) {
				$action_links = array(
										'need-help' => '<a class="thickbox" title="Send your query" href="' . admin_url('#TB_inline?max-height=420px&inlineId='. self::$wsr_prefix .'_post_query_form') .'">' . __( 'Need Help?', WSR_DOMAIN ) . '</a>'
										);

				return array_merge( $action_links, $links );
			}

			/**
			 * Language loader
			 */
			function localize() {

				$plugin_dirname = WSR_PLUGIN_DIRNAME;

				$locale = apply_filters( 'plugin_locale', get_locale(), WSR_DOMAIN );

				$loaded = load_textdomain( WSR_DOMAIN, WP_LANG_DIR . '/' . $plugin_dirname . '/' . WSR_DOMAIN . '-' . $locale . '.mo' );

				if ( ! $loaded ) {
					$loaded = load_plugin_textdomain( WSR_DOMAIN, false, $plugin_dirname . '/languages' );
				}

			}

			//  Function to add Subscription tab in WooCommerce Reports
			public function add_tab($wooreports) {

				$reports = array();
				
				$reports['subscriptions'] = array( 
													'title'  	=> __( 'Subscriptions', 'subscription_reports' ),
													'reports' 	=> array(
																		"subscription_reports" => array(
																											'title'       => '',
																											'description' => '',
																											'hide_title'  => true,
																											'callback'    => array( __CLASS__, 'subscription_reporting_page' )
																										)
																		)
												);

				$wooreports = array_merge($reports,$wooreports);
				return $wooreports;
			}

			public static function subscription_reporting_page() {
				include_once 'views/html-subscription-reports.php';
			}

			// function to handle the display of quick help widget
			public function quick_help_widget( $active_plugins = array(), $upgrader = null ) {
				
				if( ! empty( $_GET ) && ( ! empty( $_GET['page'] ) && $_GET['page'] == 'wc-reports') && ( empty( $_GET['tab'] ) || ( ! empty( $_GET['tab'] ) && $_GET['tab'] == 'subscriptions' ) ) ) {
					$active_plugins['sawsr'] = 'subscription-reports-for-woocommerce';
				} elseif ( array_key_exists( 'sawsr', $active_plugins ) ) {
		            unset( $active_plugins['sawsr'] );
		        }

		        return $active_plugins;
			}

			// Function to handle ajax calls
			public function request_handler() {

				if ( empty($_REQUEST) || empty($_REQUEST['cmd']) ) return;

				$params = (!empty($_REQUEST['params'])) ? $_REQUEST['params'] : array();

				if ( ! wp_verify_nonce( $params['security'], 'wsr-security' ) ) {
		     		return;
		     	}


		     	if ( $_REQUEST['cmd'] == 'wsr_data_sync' ) {
		     		include_once 'classes/class-subscription-reports-install.php';
		     		Subscription_Reporting_Install::sync();
					// $handler_obj = new Subscription_Reporting_Base();
		     	} else {
		     		$params['cmd'] = $_REQUEST['cmd'];
		     		$params['start_date'] = $_REQUEST['start_date'];
		     		$params['end_date'] = $_REQUEST['end_date'];
					$handler_obj = new Subscription_Reporting_Base($params);
					$handler_obj->get_cumm_stats();
		     	}
			}

		}// End of class WC_Subscription_Reporting

		/**
		 * function to initiate Subscription Reports & its functionality
		 */
		function initialize_subscription_reports() {
			$GLOBALS['woocommerce_subscription_reports'] = new WC_Subscription_Reporting();

	        if ( ! class_exists( 'StoreApps_Upgrade_1_4' ) ) {
		        require_once 'sa-includes/class-storeapps-upgrade-v-1-4.php';
		    }

	        $sku                = 'sawsr';
	        $prefix             = WC_Subscription_Reporting::$wsr_prefix;
	        $plugin_name        = 'Subscription Reports For WooCommerce';
	        $text_domain        = WSR_DOMAIN;
	        $documentation_link = '';
	        $bn_upgrader        = new StoreApps_Upgrade_1_4( __FILE__, $sku, $prefix, $plugin_name, $text_domain, $documentation_link );

		}
		add_action( 'plugins_loaded', 'initialize_subscription_reports' );

	} // End class exists check

} // End woocommerce active check

		
