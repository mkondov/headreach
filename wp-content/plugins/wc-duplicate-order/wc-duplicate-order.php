<?php

   /*
   Plugin Name: WC Duplicate Order
   Plugin URI: http://jamiegill.com
   Description: Adds a duplicate link to Woocommerce on the order actions to duplicate the existing order
   Version: 1.4
   Author: Jamie Gill
   Author URI: http://jamiegill.com
   License: GPLv2 or later
   */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    define( 'WCO_PLUGIN_DIR', dirname( __FILE__ ) );
    
    require_once WCO_PLUGIN_DIR . '/classes/class-clone-order.php';
    require_once WCO_PLUGIN_DIR . '/classes/class-clone-bulk.php';
    
    // Hooks Duplicate CTA to shop_order post type
    
    function clone_order_cta($actions, $post){
		
		
		if ($post->post_type=='shop_order') {
	        
	        $url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $post->ID );
	        
	        $copy_link = wp_nonce_url( add_query_arg( array( 'duplicate' => 'init' ), $url ), 'edit_order_nonce' );
	        
	        $actions = array_merge( $actions, 
	        	array(
	            	'duplicate' => sprintf( '<a href="%1$s">%2$s</a>',
	                	esc_url( $copy_link ), 
	                	'Duplicate'
					) 
				) 
			);
	    }
	    
	    return $actions;
				
	}
	
	add_filter( 'post_row_actions', 'clone_order_cta', 10, 2 );
    
}