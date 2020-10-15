<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Subscription_Reporting_Base' ) ) {
	class Subscription_Reporting_Base {
		public $req_params = array(),
				$domain = '',
				$cumm_kpi_data = '';

		function __construct($params) {

			if ( ! wp_verify_nonce( $params['security'], 'wsr-security' ) ) {
	     		return;
	     	}

	     	$this->domain = $params['domain'];
			$this->req_params  	= (!empty($params)) ? $params : array();
		}


		// function to update the trash status
		public static function untrash_sub( $id ) {

			$wsr_nonce = (!empty(WC_Subscription_Reporting::$wsr_nonce)) ? WC_Subscription_Reporting::$wsr_nonce : '';

			if ( ! wp_verify_nonce( $wsr_nonce, 'wsr-security' ) ) {
		 		return;
		 	}

		 	global $wpdb;

		 	//check if the subscription snapshot table exists or not
			$table_name = "{$wpdb->prefix}wsr_orders";
    		if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    			return;
    		}

    		if( empty($id) ) {
    			return;
    		}

			$post_type = get_post_type( $id );
			$valid_post_type = array("shop_order", "shop_subscription", "scheduled-action");

			if ( empty($post_type) || in_array($post_type, $valid_post_type) === FALSE ) {
				return;
			}

			$update_cond = ($post_type == 'shop_order') ? 'order_id = %d' : 'sub_id = %d';

			$query = $wpdb->prepare(" UPDATE {$wpdb->prefix}wsr_orders
										SET trash = 0
										WHERE ".$update_cond, $id);

			$wpdb->query($query);

    	}

		// function to update the trash status
		public static function trash_sub( $id ) {

			$wsr_nonce = (!empty(WC_Subscription_Reporting::$wsr_nonce)) ? WC_Subscription_Reporting::$wsr_nonce : '';

			if ( ! wp_verify_nonce( $wsr_nonce, 'wsr-security' ) ) {
		 		return;
		 	}

		 	global $wpdb;

		 	//check if the subscription snapshot table exists or not
			$table_name = "{$wpdb->prefix}wsr_orders";
    		if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    			return;
    		}

    		if( empty($id) ) {
    			return;
    		}

			$post_type = get_post_type( $id );
			$valid_post_type = array("shop_order", "shop_subscription", "scheduled-action");

			if ( empty($post_type) || in_array($post_type, $valid_post_type) === FALSE ) {
				return;
			}

			$update_cond = ($post_type == 'shop_order') ? 'order_id = %d' : 'sub_id = %d';

			$query = $wpdb->prepare(" UPDATE {$wpdb->prefix}wsr_orders
										SET trash = 1
										WHERE ".$update_cond, $id);

			$wpdb->query($query);

    	}

    	// function to delete the subscription/order
		public static function delete_sub( $id ) {

			$wsr_nonce = (!empty(WC_Subscription_Reporting::$wsr_nonce)) ? WC_Subscription_Reporting::$wsr_nonce : '';

			if ( ! wp_verify_nonce( $wsr_nonce, 'wsr-security' ) ) {
		 		return;
		 	}

		 	global $wpdb;

		 	//check if the subscription snapshot table exists or not
			$table_name = "{$wpdb->prefix}wsr_orders";
    		if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    			return;
    		}

    		if( empty($id) ) {
    			return;
    		}

			$post_type = get_post_type( $id );
			$valid_post_type = array("shop_order", "shop_subscription", "scheduled-action");

			if ( empty($post_type) || in_array($post_type, $valid_post_type) === FALSE ) {
				return;
			}

			$update_cond = ($post_type == 'shop_order') ? 'order_id = %d' : 'sub_id = %d';

			$query = $wpdb->prepare(" DELETE FROM {$wpdb->prefix}wsr_orders WHERE ".$update_cond, $id);
			$wpdb->query($query);

    	}

		// function to update the subscription status
		public static function update_sub_meta( $sub_id, $post ) {

			$wsr_nonce = (!empty(WC_Subscription_Reporting::$wsr_nonce)) ? WC_Subscription_Reporting::$wsr_nonce : '';

			if ( ! wp_verify_nonce( $wsr_nonce, 'wsr-security' ) ) {
		 		return;
		 	}

		 	global $wpdb;

			//check if the subscription snapshot table exists or not
			$table_name = "{$wpdb->prefix}wsr_orders";
    		if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    			return;
    		}

    		if( empty($sub_id) ) {
    			return;
    		}

    		$s_meta = get_post_meta($sub_id);

    		$s_interval_months = 0;

			if ( !empty($s_meta['_billing_period'][0]) && !empty($s_meta['_billing_interval'][0]) ) {
				switch ($s_meta['_billing_period'][0]) {
					case 'day':
						$s_interval_months = $s_meta['_billing_interval'][0]/30;
						break;

					case 'week':
						$s_interval_months = ($s_meta['_billing_interval'][0]*7)/30;
						break;

					case 'year':
						$s_interval_months = $s_meta['_billing_interval'][0]/12;
						break;
					
					default:
						$s_interval_months = $s_meta['_billing_interval'][0];
						break;
				}
			}

			//query for updating the sub meta

			$query = $wpdb->prepare("UPDATE {$wpdb->prefix}wsr_orders
									SET sub_billing_period = %s,
										sub_billing_interval = %d,
										sub_billing_interval_months = %d,
										sub_trial_end = %s,
										sub_end = %s
									WHERE sub_id = %d", $s_meta['_billing_period'][0],
											$s_meta['_billing_interval'][0], $s_interval_months,
											$s_meta['_schedule_trial_end'][0], $s_meta['_schedule_end'][0],
											$sub_id);

			$wpdb->query($query);
    	}

		// function to update the subscription status
		public static function update_sub_status( $sub_id, $old_status, $new_status ) {

			$wsr_nonce = (!empty(WC_Subscription_Reporting::$wsr_nonce)) ? WC_Subscription_Reporting::$wsr_nonce : '';

			if ( ! wp_verify_nonce( $wsr_nonce, 'wsr-security' ) ) {
		 		return;
		 	}

		 	global $wpdb;

			//check if the subscription snapshot table exists or not
			$table_name = "{$wpdb->prefix}wsr_orders";
    		if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    			return;
    		}

    		if( empty($sub_id) || empty($new_status) ) {
    			return;
    		}

    		$query = $wpdb->prepare("UPDATE {$wpdb->prefix}wsr_orders
    								SET sub_status = %s
									WHERE sub_id = %d", 'wc-'.$new_status, $sub_id);
			$wpdb->query($query);
		}

		// function to handle syncing of data on order events
		public static function add_renewal_order( $renewal_order, $subscription = '' ) {

			$wsr_nonce = (!empty(WC_Subscription_Reporting::$wsr_nonce)) ? WC_Subscription_Reporting::$wsr_nonce : '';

			if ( ! wp_verify_nonce( $wsr_nonce, 'wsr-security' ) ) {
		 		return $renewal_order;
		 	}

		 	global $wpdb;

		 	//check if the subscription snapshot table exists or not
			$table_name = "{$wpdb->prefix}wsr_orders";
    		if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    			return $renewal_order;
    		}


			self::add_order($renewal_order->id, '','renewal');

			return $renewal_order;

		}

		// function to handle syncing of data on order events
		public static function add_order( $order_id, $refund_id = '', $sub_action = 'signup' ) {

			$wsr_nonce = (!empty(WC_Subscription_Reporting::$wsr_nonce)) ? WC_Subscription_Reporting::$wsr_nonce : '';

			if ( ! wp_verify_nonce( $wsr_nonce, 'wsr-security' ) ) {
		 		return;
		 	}

			global $wpdb;

			//check if the subscription snapshot table exists or not
			$table_name = "{$wpdb->prefix}wsr_orders";
    		if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    			return;
    		}

    		$parent_order_items = array();

			$order_id = (!empty( $refund_id )) ? $refund_id : $order_id;
			$type = (!empty($refund_id)) ? 'shop_order_refund' : 'shop_order';

			$order = new WC_Order( $order_id );
			$order_items = $order->get_items();
			$order_meta = get_post_meta($order_id);

			//for manual refuds - getting parent order item count
			if( $type == 'shop_order_refund' ) {
				$parent_order = new WC_Order( $order->post->post_parent );
				$parent_order_items = $parent_order->get_items();
			}

			$subscriptions = wcs_get_subscriptions_for_order( $order_id );

			// for handling sub switch
			$sub_action = (!empty($order_meta['_subscription_switch'][0])) ? 'switch' : $sub_action;

			$values = array();

			if( !empty( $refund_id ) && empty($order_items) && sizeof($parent_order_items) == 1 ) { // For handling manual refunds done at order level
				
				foreach ($parent_order_items as $parent_order_item) {
					$order_cond = " AND ". (( $refund_id != 'renewal' ) ? "order_id = ".$order->post->post_parent : "sub_id = ".( (!empty($order_meta['_subscription_renewal'][0])) ? $order_meta['_subscription_renewal'][0] : $order_meta['_subscription_switch'][0] ));
					$order_cond = (!empty($order_meta['_subscription_switch'][0]) && $sub_action == 'switch') ? " AND sub_id = ".$order_meta['_subscription_switch'][0] : $order_cond; //added for handling sub. switch
					

					$v_id_cond = ($sub_action != 'switch' && !empty($parent_order_item['variation_id'])) ? " AND v_id = ".$parent_order_item['variation_id'] : '';

					$query = "SELECT * FROM {$wpdb->prefix}wsr_orders
										WHERE p_id = ".$parent_order_item['product_id'].
											$v_id_cond."".
											$order_cond;
					$sub_details = $wpdb->get_results ($query, 'ARRAY_A');

					if ( empty($sub_details) ) {
						continue;
					}

					$switch = 0;
					$renewal = ( $sub_action == 'renewal' ) ? 1 : 0;
					$price = (!empty($parent_order_item['variation_id'])) ? get_post_meta($parent_order_item['variation_id'], '_price', true) : get_post_meta($parent_order_item['product_id'], '_price', true);

					$values[] = "( " .$wpdb->_real_escape($sub_details[0]['sub_id']). ", " .
										$wpdb->_real_escape($order->id). ", " .
										$wpdb->_real_escape($parent_order_item['product_id']). ", " .
										$wpdb->_real_escape($parent_order_item['variation_id']). ", '" .
										$wpdb->_real_escape(date("Y-m-d", strtotime($order->order_date))). "', '" .
										$wpdb->_real_escape(date("H:i:s", strtotime($order->order_date))). "', '" .
										$wpdb->_real_escape($type). "', '" .
										$wpdb->_real_escape($order->post_status). "', '" .
										$wpdb->_real_escape($sub_details[0]['sub_status']). "', " .
										$wpdb->_real_escape($sub_details[0]['user_id']). ", '" .
										$wpdb->_real_escape($sub_details[0]['cust_email']). "', '" .
										$wpdb->_real_escape($sub_details[0]['cust_name']). "', " .
										$wpdb->_real_escape($sub_details[0]['qty']). ", " .
										$wpdb->_real_escape($order_meta['_order_total'][0]). ", " .
										$wpdb->_real_escape($price). ", '" .
										$wpdb->_real_escape($order_meta['_order_currency'][0]). "', '" .
										$wpdb->_real_escape(!empty($order_meta['_payment_method'][0]) ? $order_meta['_payment_method'][0] : $sub_details[0]['payment_method']). "', '" .
										$wpdb->_real_escape($sub_details[0]['sub_billing_period']). "', " .
										$wpdb->_real_escape($sub_details[0]['sub_billing_interval']). ", " .
										$wpdb->_real_escape($sub_details[0]['sub_billing_interval_months']). ", '" .
										$wpdb->_real_escape($sub_details[0]['sub_trial_end']). "', '" .
										$wpdb->_real_escape($sub_details[0]['sub_end']). "', " .
										$wpdb->_real_escape($renewal). ", " .
										$wpdb->_real_escape($switch).", 0 )";	
				}
				



			} else if( !empty( $refund_id ) || empty($subscriptions) || ($sub_action == 'renewal' || $sub_action == 'switch') ) {
				foreach ( $order_items as $item ) {
					if ( $item['qty'] != 0 || $item['line_total'] != 0 || ($sub_action == 'renewal' || $sub_action == 'switch') || empty($subscriptions) ) {
 
						if ( empty($subscriptions) ) {
							// $order_cond = " AND ". (( empty($order_meta['_subscription_renewal'][0]) ) ? "order_id = ".$order->post->post_parent : "sub_id = ".$order_meta['_subscription_renewal'][0]);
							$order_cond = " AND ". (( !empty($order_meta['_subscription_renewal'][0]) || !empty($order_meta['_subscription_switch'][0]) ) ? "sub_id = ". ( (!empty($order_meta['_subscription_renewal'][0])) ? $order_meta['_subscription_renewal'][0] : $order_meta['_subscription_switch'][0] )  : "order_id = ".$order->post->post_parent);
						} else {
							// $order_cond = " AND ". (( $refund_id != 'renewal' ) ? "order_id = ".$order->post->post_parent : "sub_id = ".$order_meta['_subscription_renewal'][0]);
							$order_cond = " AND ". (( $refund_id != 'renewal' ) ? "order_id = ".$order->post->post_parent : "sub_id = ".( (!empty($order_meta['_subscription_renewal'][0])) ? $order_meta['_subscription_renewal'][0] : $order_meta['_subscription_switch'][0] ));
							$order_cond = (!empty($order_meta['_subscription_switch'][0]) && $sub_action == 'switch') ? " AND sub_id = ".$order_meta['_subscription_switch'][0] : $order_cond; //added for handling sub. switch
						}

						$v_id_cond = ($sub_action != 'switch' && !empty($item['variation_id'])) ? " AND v_id = ".$item['variation_id'] : '';

						$query = "SELECT * FROM {$wpdb->prefix}wsr_orders
											WHERE p_id = ".$item['product_id'].
												$v_id_cond."".
												$order_cond;
						$sub_details = $wpdb->get_results ($query, 'ARRAY_A');

						if ( empty($sub_details) ) {
							continue;
						}

						$switch = 0;
						if( $sub_action == 'switch' ) {

							$query = $wpdb->prepare("SELECT MAX(order_id) 
																	FROM {$wpdb->prefix}wsr_orders
																	WHERE sub_id = %d
																		AND order_id < %d", $sub_details[0]['sub_id'], $order->id);
							$switch = $wpdb->get_var($query);
						}

						$renewal = ( $sub_action == 'renewal' ) ? 1 : 0;
						$price = (!empty($item['variation_id'])) ? get_post_meta($item['variation_id'], '_price', true) : get_post_meta($item['product_id'], '_price', true);

						$values[] = "( " .$wpdb->_real_escape($sub_details[0]['sub_id']). ", " .
											$wpdb->_real_escape($order->id). ", " .
											$wpdb->_real_escape($item['product_id']). ", " .
											$wpdb->_real_escape($item['variation_id']). ", '" .
											$wpdb->_real_escape(date("Y-m-d", strtotime($order->order_date))). "', '" .
											$wpdb->_real_escape(date("H:i:s", strtotime($order->order_date))). "', '" .
											$wpdb->_real_escape($type). "', '" .
											$wpdb->_real_escape($order->post_status). "', '" .
											$wpdb->_real_escape($sub_details[0]['sub_status']). "', " .
											$wpdb->_real_escape(!empty($order_meta['_customer_user'][0]) ? $order_meta['_customer_user'][0] : 0). ", '" .
											$wpdb->_real_escape(!empty($order_meta['_billing_email'][0]) ? $order_meta['_billing_email'][0] : $sub_details[0]['cust_email'] ). "', '" .
											$wpdb->_real_escape( (!empty($order_meta['_billing_first_name'][0]) && !empty($order_meta['_billing_last_name'][0])) ? $order_meta['_billing_first_name'][0] .' '. $order_meta['_billing_last_name'][0] : $sub_details[0]['cust_name'] ) . "', " .
											$wpdb->_real_escape($item['qty']). ", " .
											$wpdb->_real_escape($item['line_total']). ", " .
											$wpdb->_real_escape($price). ", '" .
											$wpdb->_real_escape($order_meta['_order_currency'][0]). "', '" .
											$wpdb->_real_escape(!empty($order_meta['_payment_method'][0]) ? $order_meta['_payment_method'][0] : $sub_details[0]['payment_method']). "', '" .
											$wpdb->_real_escape($sub_details[0]['sub_billing_period']). "', " .
											$wpdb->_real_escape($sub_details[0]['sub_billing_interval']). ", " .
											$wpdb->_real_escape($sub_details[0]['sub_billing_interval_months']). ", '" .
											$wpdb->_real_escape($sub_details[0]['sub_trial_end']). "', '" .
											$wpdb->_real_escape($sub_details[0]['sub_end']). "', " .
											$wpdb->_real_escape($renewal). ", " .
											$wpdb->_real_escape($switch).", 0 )";
					}
				}
	    	} else {

				foreach ( $subscriptions as $id => $sub ) {

					$s_items = $sub->get_items();
					$s_meta = get_post_meta($id);

					$s_interval_months = 0;

					if ( !empty($s_meta['_billing_period'][0]) && !empty($s_meta['_billing_interval'][0]) ) {
						switch ($s_meta['_billing_period'][0]) {
							case 'day':
								$s_interval_months = $s_meta['_billing_interval'][0]/30;
								break;

							case 'week':
								$s_interval_months = ($s_meta['_billing_interval'][0]*7)/30;
								break;

							case 'year':
								$s_interval_months = $s_meta['_billing_interval'][0]/12;
								break;
							
							default:
								$s_interval_months = $s_meta['_billing_interval'][0];
								break;
						}
					}

					if ( empty($s_items) ) {
						continue;
					}

					foreach ( $s_items as $item_id => $item ) {

						$qty = $tot = 0;
						$p_id = $item['product_id'];
						$v_id = $item['variation_id'];

						//code for getting the item total & qty
						foreach ( $order_items as $key => $value ) {
							if ( $value['product_id'] == $p_id && $value['variation_id'] == $v_id ) {
								$tot = $value['line_total'];
								$qty = $value['qty'];
							}
						}

						$price = (!empty($v_id)) ? get_post_meta($v_id, '_price', true) : get_post_meta($p_id, '_price', true);

						$values[] = "( " .$wpdb->_real_escape($id). ", " .
										$wpdb->_real_escape($sub->order->id). ", " .
										$wpdb->_real_escape($p_id). ", " .
										$wpdb->_real_escape($v_id). ", '" .
										$wpdb->_real_escape(date("Y-m-d", strtotime($sub->order->order_date))). "', '" .
										$wpdb->_real_escape(date("H:i:s", strtotime($sub->order->order_date))). "', '" .
										$wpdb->_real_escape($type). "', '" .
										$wpdb->_real_escape($sub->order->post_status). "', '" .
										$wpdb->_real_escape($sub->post->post_status). "', " .
										$wpdb->_real_escape(!empty($order_meta['_customer_user'][0]) ? $order_meta['_customer_user'][0] : 0). ", '" .
										$wpdb->_real_escape($order_meta['_billing_email'][0]). "', '" .
										$wpdb->_real_escape($order_meta['_billing_first_name'][0]) .' '. $wpdb->_real_escape($order_meta['_billing_last_name'][0]). "', " .
										$wpdb->_real_escape($qty). ", " .
										$wpdb->_real_escape($tot). ", " .
										$wpdb->_real_escape($price). ", '" .
										$wpdb->_real_escape($order_meta['_order_currency'][0]). "', '" .
										$wpdb->_real_escape($order_meta['_payment_method'][0]). "', '" .
										$wpdb->_real_escape($s_meta['_billing_period'][0]). "', " .
										$wpdb->_real_escape($s_meta['_billing_interval'][0]). ", " .
										$wpdb->_real_escape($s_interval_months). ", '" .
										$wpdb->_real_escape( (!empty($s_meta['_schedule_trial_end'][0]) ? date("Y-m-d", strtotime($s_meta['_schedule_trial_end'][0])) : '') ). "', '" .
										$wpdb->_real_escape( (!empty($s_meta['_schedule_end'][0]) ? date("Y-m-d", strtotime($s_meta['_schedule_end'][0])) : '') ). "', " .
										$wpdb->_real_escape( (!empty($order_meta['_subscription_renewal'][0]) ? 1 : 0 ) ). ", " .
										$wpdb->_real_escape( (!empty($order_meta['_subscription_switch'][0]) ? 1 : 0 ) ). ", 0 )";
					}
				}
	    	}

			$query = "REPLACE INTO {$wpdb->prefix}wsr_orders VALUES ";

			if ( count($values) > 0 ) {
				$query .= implode(',',$values);
				$wpdb->query( $query );
			}
		}

		// function to get the kpi data
		public static function get_kpi_data() {

			$wsr_nonce = (!empty(WC_Subscription_Reporting::$wsr_nonce)) ? WC_Subscription_Reporting::$wsr_nonce : '';

			if ( ! wp_verify_nonce( $wsr_nonce, 'wsr-security' ) ) {
		 		return json_encode(array());
		 	}

			global $wpdb;

			//check if the subscription snapshot table exists or not
			$table_name = "{$wpdb->prefix}wsr_orders";
    		if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    			return json_encode(array());
    		}

			$c_date = current_time( 'Y-m-d' );


			$kpi_data = array();
			$kpi = array('subscribers', 'MRR');

			foreach ( $kpi as $key ) {
				$kpi_data [$key] = array();
				$kpi_data [$key] ['title'] = __(ucwords(str_replace('_', ' ', $key)), WSR_DOMAIN);
				$kpi_data [$key] ['val'] = 0;
			}

			$currency_symbol  = defined('WSR_CURRENCY_SYMBOL') ? WSR_CURRENCY_SYMBOL : get_woocommerce_currency_symbol();
			$decimal_places   = defined('WSR_DECIMAL_PLACES') ? WSR_DECIMAL_PLACES : get_option( 'woocommerce_price_num_decimals' );
			$thousand_sep   = defined('WSR_THOUSAND_SEP') ? WSR_THOUSAND_SEP : get_option( 'woocommerce_price_thousand_sep', ',' );
			$decimal_sep   = defined('WSR_DECIMAL_SEP') ? WSR_DECIMAL_SEP : get_option( 'woocommerce_price_decimal_sep', '.' );

			// query to get total subscribers and total MRR

			$query = "SELECT IFNULL(COUNT( DISTINCT(sub_id)), 0) AS subscribers,
							IFNULL(SUM( CASE WHEN total > 0 
						                 THEN total*sub_billing_interval_months END), 0) AS MRR
					FROM {$wpdb->prefix}wsr_orders
					WHERE type = 'shop_order'
						AND sub_status = 'wc-active'
						AND is_renewal = 0
						AND sub_switch = 0
						AND trash = 0";

			$results = $wpdb->get_results( $query , 'ARRAY_A');


			if ( count($results) > 0 ) {
				$mrr = number_format ( $results[0]['MRR'], $decimal_places, $decimal_sep, $thousand_sep );
				list($int, $dec) = explode('.', $mrr);

				$kpi_data['MRR']['val'] = '<span class="wsr_kpi_cur_dec" >'. $currency_symbol .'</span
											><span>'. $int .'.</span
											><span class="wsr_kpi_cur_dec" >'. $dec .'</span>';
				$kpi_data['subscribers']['val'] = number_format ( $results[0]['subscribers'], 0, $decimal_sep, $thousand_sep );
			}

			return json_encode ($kpi_data);

		}


		//Formatting the cumm kpi data



		public function format_kpi_data() {

		    $returns = array();

			if (empty($this->cumm_kpi_data)) {
				return;
			}

		    foreach ( $this->cumm_kpi_data['kpi'] as $kpi => $val ) {

		    	// code for calculating the cmp. value
		    	if ( !empty($val['params']['diff_format']) && $val['params']['diff_format'] == '$' ) {
					$diff = number_format ( abs(round(($val['cp'] - $val['lp']),2)), $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] );
				} else if ( !empty($val['params']['diff_format']) && $val['params']['diff_format'] == '%' ) {
					$diff = (!empty($val['lp']) && $val['lp'] > 0 ) ? abs(round(((($val['cp'] - $val['lp'])/$val['lp']) * 100),2)) : round($val['cp'],2);
					$diff = number_format ( $diff, $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] ). '%';
				} else {
					$diff = '';
				}

				if ( $diff != 0 ) {
					if ( $val['lp'] < $val['cp'] ) {
						if ( $kpi == 'user_churn_rate' || $kpi == 'subscription_refunds' || $kpi == 'refunds' ) {
							$img = $this->req_params ['img_up'];
							$color = 'red';
						} else {
							$img = $this->req_params ['img_up'];
							$color = 'green';
						}
					}
					else {
						if ( $kpi == 'user_churn_rate' || $kpi == 'subscription_refunds' || $kpi == 'refunds' ) {
							$img = $this->req_params ['img_down'];
							$color = 'green';	
						} else {
					    	$img = $this->req_params ['img_down'];
					    	$color = 'red';
					    }
					}    
				} else {
					$diff = "";
					$img = "";
				}

				$f_val = number_format ( $val['cp'], $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] );
				list($int, $dec) = explode($this->req_params ['decimal_sep'], $f_val);


				list($dint, $ddec) = (!empty($diff)) ? explode($this->req_params ['decimal_sep'], $diff) : array(0,0);


	            $returns[$kpi] = ( !empty($diff) && !empty($img) ) ? '<div class = "wsr_kpi_trend '. $color .'"> ' .
																		'<i class= "'. $img .'" ></i>' . 
	                              										'<h2 class = "wsr_cumm_comp_price '. $color .'"> 
	                              											<span>'. $dint .''.$this->req_params ['decimal_sep'].'</span
																			><span class="wsr_cumm_kpi_comp_cur_dec" >'. $ddec .'</span> </h2> 
	                             										</div>' : '';

	            $returns[$kpi] .= '<h3 class="wsr_kpi_text wsr_cumm_kpi_text "> '. $val['title'] .' </h3>';

	            if ( !empty($val['params']['cp_format']) && $val['params']['cp_format'] == '$' ) {
					$returns[$kpi] .= '<h1 class="wsr_kpi_price wsr_cumm_kpi_price "> 
											<span class="wsr_cumm_kpi_cur_dec" >'. $this->req_params ['currency_symbol'] .'</span
											><span>'. $int .''.$this->req_params ['decimal_sep'].'</span
											><span class="wsr_cumm_kpi_cur_dec" >'. $dec .'</span>
										</h1>';
				} else if ( !empty($val['params']['cp_format']) && $val['params']['cp_format'] == '%' ) {
					$returns[$kpi] .= '<h1 class="wsr_kpi_price wsr_cumm_kpi_price "> 
											<span>'. $int .''.$this->req_params ['decimal_sep'].'</span
											><span class="wsr_cumm_kpi_cur_dec" >'. $dec .'%</span>
										</h1>';
				} else {
					$f_val = number_format ( $val['cp'], 0, $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] );
					$returns[$kpi] .= '<h1 class="wsr_kpi_price wsr_cumm_kpi_price "> '. $f_val .' </h1>';
				}

	            

		    }

			$this->cumm_kpi_data['kpi'] = $returns;
		}


		//function to get the product title
		private function get_prod_title($data, $ids) {

	     	global $wpdb;

	     	if( empty($ids) || empty($data) ) {
	     		return;
	     	}

			if( count($ids['t_p_ids']) > 0 ) {
				$query = "SELECT id,
							CASE WHEN post_parent = 0 THEN post_title END as title
						FROM {$wpdb->prefix}posts
						WHERE id IN (". implode(",",$ids['t_p_ids']) .")
						GROUP BY id";
				$results = $wpdb->get_results($query, 'ARRAY_A'); 

				if ( count($results) > 0 ) {

					$v_ids = $ids['t_v_ids'];

					foreach ( $results as $row ) {

						// assigning products titles
						foreach ( $data as $key => &$arr ) {

							$index = array_search($row['id'], $v_ids);

							if ( !empty($row['id']) && $row['id'] == $key ) {
								$arr['title'] = $row['title'];
							} else if ( !empty($index) && $index == $key ) {
								$arr['title'] = $row['title'];
								unset($v_ids[$index]);
							}
						}
					}
				}
			}

			if( count($ids['t_v_ids']) > 0 ) { 
				//Code to get the attribute terms for all attributes
		        $query = "SELECT terms.name AS name,
	                            terms.slug AS slug,
	                            taxonomy.taxonomy as taxonomy
	                        FROM {$wpdb->prefix}terms as terms
	                            JOIN {$wpdb->prefix}term_taxonomy as taxonomy ON (taxonomy.term_id = terms.term_id)
	                        WHERE taxonomy.taxonomy LIKE 'pa_%'
	                        GROUP BY taxonomy.taxonomy, terms.slug";
		        $results = $wpdb->get_results( $query, 'ARRAY_A' );

		        $p_att = array();

		        if ( count($results) > 0 ) {
		        	foreach ($results as $row) {
			            if ( empty($p_att[$row['taxonomy']]) ) {
			                $p_att[$row['taxonomy']] = array();               
			            }
			            $p_att[$row['taxonomy']][$row['slug']] = $row['name'];
			        }	
		        }

		        //  code to get the attribute labels
		        $query = "SELECT attribute_name, attribute_label
		                    FROM {$wpdb->prefix}woocommerce_attribute_taxonomies";
		        $results = $wpdb->get_results( $query, 'ARRAY_A' );

		        $a_lbl = array();

		        if ( count($results) > 0 ) {
		        	foreach ($results as $row) {
			            $a_lbl['pa_' . $row['attribute_name']] = $row['attribute_label'];
			        }	
		        }

		        // code to get the variation att.
		        $query = "SELECT post_id as id,
		        			meta_key as mkey,
		        			meta_value as value
		        		FROM {$wpdb->prefix}postmeta
		        		WHERE meta_key LIKE 'attribute_%'
		        			AND post_id IN (". implode(",", array_keys($ids['t_v_ids']) ) .")
		        		GROUP BY id, mkey";
		        $results = $wpdb->get_results( $query, 'ARRAY_A' );

		        if ( count($results) > 0 ) {

		        	$id = $results[0]['id'];
		        	$vt = '';

		        	for ($i=0; $i<count($results); $i++) {

		        		$a = ( strpos($results[$i]['mkey'], 'attribute_') !== false ) ? substr($results[$i]['mkey'], strlen('attribute_')) : '';

		        		$vt .= ' - ';
		        		$vt .= (!empty($a_lbl[$a])) ? $p_att[$a][$results[$i]['value']] : $results[$i]['value'];

		        		$id = $results[$i]['id'];

		        		if ( ( !empty($results[$i+1]) && $id != $results[$i+1]['id']) || $i == (count($results)-1) ) {

		        			// assigning variation attributes to titles
							foreach ( $data as $key => &$arr ) {
								if ( !empty($results[$i]['id']) && $results[$i]['id'] == $key ) {
									$arr['title'] .= $vt;
								}
							}

							$vt = '';
		        		}
		        	}
		        }
			}

			return $data;
		}

		// function to get the cumm data
		public function query_cumm_stats($cumm_dates, $date_series) {
			
			global $wpdb;

		    $this->cumm_kpi_data = array('kpi' => array(), 
		    							'chart' => array(),
		    							'meta' => array('start_date' => $cumm_dates['cp_start_date'],
		    											'end_date' => $cumm_dates['cp_end_date'],
		    											'date_format' => $cumm_dates['format'] ));

		    $this->cumm_kpi_data['chart']['period'] = $date_series['cp'];
			$periods_count = count($this->cumm_kpi_data['chart']['period']);
			$p2i = array_flip($this->cumm_kpi_data['chart']['period']);
			$lp2i = array_flip($date_series['lp']);

			$this->cumm_kpi_data['chart']['tip_format'] = array(); //for chart tooltip formatting

			$time_str = ( $cumm_dates['format'] == '%H' ) ? ':00:00' : '';
			$order_date_cond = (!empty($time_str)) ? " ,concat(DATE_FORMAT(order_time, '%s'), '".$time_str."') AS order_date " : " ,concat(DATE_FORMAT(order_date, '%s'), '".$time_str."') AS order_date ";

			// $kpi = array('monthly_recurring_revenue_(MRR)', 'total_revenue', 'MRR_contribution_to_revenue', 
			// 			'total_subscription_revenue', 'annual_run_rate', 'new_signups',
			// 			'user_churn_rate', 'avg._revenue_per_paid_user_(ARPPU)', 'life_time_value_(LTV)', 
			// 			'no._of_upgrades', 'upgrade_revenue', 'no._of_downgrades', 
			// 			'downgrade_revenue', 'subscription_refunds', 'refunds', 
			// 			'active', 'switched', 'suspended_/_on_hold', 'cancelled_/_expired', 
			// 			'trials', 'paid', 'trials_to_paid' );

			$kpi = array('monthly_recurring_revenue_(MRR)', 'user_churn_rate', 'avg._revenue_per_paid_user_(ARPPU)',
						'annual_run_rate', 'MRR_contribution_to_revenue', 'life_time_value_(LTV)', 
						'total_revenue', 'total_subscription_revenue', 'subscription_refunds',
						'refunds', 'no._of_upgrades', 'upgrade_revenue',
						'new_signups', 'no._of_downgrades', 'downgrade_revenue',
						'active', 'switched', 'suspended_/_on_hold',
						'cancelled_/_expired', 'trials', 'paid',
						'trials_to_paid' );

			$currency_symbol_hide = array('new_signups', 'no._of_upgrades', 'no._of_downgrades', 'active', 'switched', 'suspended_/_on_hold', 'cancelled_/_expired', 'trials', 'paid', 'trials_to_paid');

			foreach ( $kpi as $key ) {
				$this->cumm_kpi_data['kpi'] [$key] = array();
				$this->cumm_kpi_data['kpi'] [$key] ['title'] = __(ucwords(str_replace('_', ' ', $key)), $this->domain);
				$this->cumm_kpi_data['kpi'] [$key] ['cp'] = 0;
				$this->cumm_kpi_data['kpi'] [$key] ['lp'] = 0;

				$this->cumm_kpi_data['kpi'] [$key] ['params'] = array();
				$this->cumm_kpi_data['kpi'] [$key] ['params'] ['cp_format'] = (in_array($key, $currency_symbol_hide)) ? 'none' : (($key == 'MRR_contribution_to_revenue' || $key == 'user_churn_rate') ? '%' : '$' );
				$this->cumm_kpi_data['kpi'] [$key] ['params'] ['diff_format'] = '%';

				$this->cumm_kpi_data['chart']['tip_format'][$key] = (in_array($key, $currency_symbol_hide)) ? 'none' : (($key == 'MRR_contribution_to_revenue' || $key == 'user_churn_rate') ? '%' : $this->req_params ['currency_symbol'] );
			}

			// Initialize chart data to 0
			foreach ($kpi as $value) {
				$this->cumm_kpi_data['chart'][$value] = array_fill(0, $periods_count, 0);
			}

			// ###############################
			// MRR and Refunds
			// ###############################


			$query = $wpdb->prepare( "SELECT CASE 
												WHEN order_date >= %s THEN 'CP' 
												WHEN order_date >= %s THEN 'LP' 
											END AS period,

										IFNULL(SUM( CASE WHEN order_status != 'wc-refunded' 
										                 THEN total*sub_billing_interval_months END), 0) AS mrr,
										IFNULL(SUM( CASE WHEN order_status != 'wc-refunded' THEN total 
													END), 0) AS sub_tot,
										IFNULL(SUM( CASE WHEN order_status = 'wc-refunded' AND sub_switch = 0 THEN total 
													END), 0) AS sub_refunds

										$order_date_cond

										FROM {$wpdb->prefix}wsr_orders
										WHERE order_status IN ('wc-on-hold','wc-processing','wc-completed','wc-refunded')
											AND type = 'shop_order'
											AND (order_date BETWEEN %s AND %s OR order_date BETWEEN %s AND %s)
											AND trash = 0
										GROUP BY order_date, period", $cumm_dates['cp_start_date'], $cumm_dates['lp_start_date'], $cumm_dates['format'],
															$cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
															$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

			$results = $wpdb->get_results( $query , 'ARRAY_A');

			//query to get the total partial refunds
			$query = $wpdb->prepare( "SELECT CASE 
											WHEN wsr.order_date >= %s THEN 'CP' 
											WHEN wsr.order_date >= %s THEN 'LP' 
										END AS period,

									IFNULL(SUM( wsr.total*wsr.sub_billing_interval_months ), 0) AS mrr,
									IFNULL(SUM( wsr.total ), 0) AS sub_tot,
									IFNULL(SUM( -1*wsr.total ), 0) AS sub_refunds

									$order_date_cond

									FROM {$wpdb->prefix}wsr_orders AS wsr
										JOIN {$wpdb->prefix}posts AS p ON (p.id = wsr.order_id 
																		AND p.post_type = 'shop_order_refund')
									WHERE (wsr.order_date BETWEEN %s AND %s OR wsr.order_date BETWEEN %s AND %s)
										AND wsr.trash = 0
										AND p.post_parent NOT IN (SELECT id from {$wpdb->prefix}posts WHERE post_type = 'shop_order' AND post_status = 'wc-refunded')
									GROUP BY order_date, period", $cumm_dates['cp_start_date'], $cumm_dates['lp_start_date'], $cumm_dates['format'],
														$cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
														$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

			$results_partial_refund = $wpdb->get_results( $query , 'ARRAY_A');

			$tot_partial_refunds = array('LP' => array(), 'CP' => array());

			if ( count($results_partial_refund) > 0 ) {
				foreach ( $results_partial_refund as $row ) {
					$tot_partial_refunds[$row['period']][$row['order_date']] = array( 'mrr' => $row['mrr'], 
																					'sub_tot' => $row['sub_tot'],
																					'sub_refunds' => $row['sub_refunds'] );
				}
			}

			$cp_mrr = $cp_mrr_count = $lp_mrr = $lp_mrr_count = 0;

			if ( count($results) > 0 ) {
				foreach ( $results as $row ) {

					$rmrr = (!empty($tot_partial_refunds[$row['period']][$row['order_date']]['mrr'])) ? $tot_partial_refunds[$row['period']][$row['order_date']]['mrr'] : 0;
					$rtot = (!empty($tot_partial_refunds[$row['period']][$row['order_date']]['sub_tot'])) ? $tot_partial_refunds[$row['period']][$row['order_date']]['sub_tot'] : 0;
					$partial_refund = (!empty($tot_partial_refunds[$row['period']][$row['order_date']]['sub_refunds'])) ? $tot_partial_refunds[$row['period']][$row['order_date']]['sub_refunds'] : 0;

					if( $row['period'] == 'CP' ){

						if (!array_key_exists($row['order_date'], $p2i) ) {
							error_log('WooCommerce Subscription Reporting: Invalid value for "order_date" in DB results - '.$row['order_date']);
							continue;
						}

						// Index of this period - this will be used to position different chart data at this period's index
						$i = $p2i[ $row['order_date'] ];

						//KPI Data
						if( !empty($p2i) && sizeof($p2i)-1 == $i ) {
							$cp_mrr += $row['mrr'];
						}

						$cp_mrr_count++;

						$this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp'] += ($row['mrr'] + $rmrr);
						$this->cumm_kpi_data['kpi'] ['total_subscription_revenue'] ['cp'] += ($row['sub_tot'] + $rtot);
						$this->cumm_kpi_data['kpi'] ['subscription_refunds'] ['cp'] += ($row['sub_refunds'] + $partial_refund);

						//Chart Data
						$this->cumm_kpi_data['chart'] ['monthly_recurring_revenue_(MRR)'][ $i ] += round ( ($row['mrr'] + $rmrr), $this->req_params ['decimal_places'] );
						$this->cumm_kpi_data['chart'] ['total_subscription_revenue'][ $i ] += round ( ($row['sub_tot'] + $rtot), $this->req_params ['decimal_places'] );
						$this->cumm_kpi_data['chart'] ['subscription_refunds'][ $i ] += round ( ($row['sub_refunds'] + $partial_refund), $this->req_params ['decimal_places'] );

					} else if( $row['period'] == 'LP' ){

						if (!array_key_exists($row['order_date'], $lp2i) ) {
							continue;
						}

						// Index of this period - this will be used to position different chart data at this period's index
						$i = $lp2i[ $row['order_date'] ];

						if( !empty($lp2i) && sizeof($lp2i)-1 == $i ) {
							$lp_mrr += $row['mrr'];
						}

						$lp_mrr_count++;

						$this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp'] += ($row['mrr'] + $rmrr);
						$this->cumm_kpi_data['kpi'] ['total_subscription_revenue'] ['lp'] += ($row['sub_tot'] + $rtot);
						$this->cumm_kpi_data['kpi'] ['subscription_refunds'] ['lp'] += ($row['sub_refunds'] + $partial_refund);
					}
				}
			}

			$this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp_tot'] = $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp'];
			$this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp_tot'] = $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp'];

			$cp_months = $lp_months = 1;

			if( $cumm_dates['cp_diff_dates'] > 30 && version_compare(PHP_VERSION, '5.3.0') ) {
				$d1 = new DateTime($cumm_dates['cp_start_date']);
				$d2 = new DateTime($cumm_dates['cp_end_date']);

				$cp_months = $d1->diff($d2)->m + 1;

				$this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp'] = (!empty($this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp']) && !empty($cp_months)) ? $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp']/$cp_months : $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp'];

				$d1 = new DateTime($cumm_dates['lp_start_date']);
				$d2 = new DateTime($cumm_dates['lp_end_date']);

				$lp_months = $d1->diff($d2)->m + 1;

				$this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp'] = (!empty($this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp']) && !empty($lp_months)) ? $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp']/$lp_months : $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp'];
			}

			// ###############################
			// Tot. Revenue + Refunds
			// ###############################

			//query to get the tot. revenue & refunds
			$query = $wpdb->prepare( "SELECT CASE 
												WHEN DATE(p.post_date) >= %s THEN 'CP' 
												WHEN DATE(p.post_date) >= %s THEN 'LP' 
											END AS period,
											concat(DATE_FORMAT(p.post_date, '%s'), '".$time_str."') AS order_date,

											IFNULL(SUM( CASE WHEN p.post_status != 'wc-refunded' THEN pm.meta_value
														END), 0) AS tot,
											IFNULL(SUM( CASE WHEN p.post_status = 'wc-refunded' THEN pm.meta_value 
														END), 0) AS refunds

										FROM {$wpdb->prefix}postmeta as pm
											JOIN {$wpdb->prefix}posts as p ON (p.id = pm.post_id 
																				AND pm.meta_key = '_order_total'
																				AND p.post_type = 'shop_order'
																				AND p.post_status IN ('wc-on-hold','wc-processing','wc-completed','wc-refunded'))
										WHERE DATE(p.post_date) BETWEEN %s AND %s
													OR DATE(p.post_date) BETWEEN %s AND %s
										GROUP BY order_date, period", $cumm_dates['cp_start_date'], $cumm_dates['lp_start_date'], $cumm_dates['format'],
															$cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
															$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

			$results = $wpdb->get_results( $query , 'ARRAY_A');

			//query to get the tot. partial refunds
			$query = $wpdb->prepare( "SELECT CASE 
												WHEN DATE(p.post_date) >= %s THEN 'CP' 
												WHEN DATE(p.post_date) >= %s THEN 'LP' 
											END AS period,
											concat(DATE_FORMAT(p.post_date, '%s'), '".$time_str."') AS order_date,

											IFNULL(SUM(pm.meta_value), 0) AS tot,
											IFNULL(SUM(-1*pm.meta_value), 0) AS refunds

										FROM {$wpdb->prefix}postmeta as pm
											JOIN {$wpdb->prefix}posts as p ON (p.id = pm.post_id 
																				AND pm.meta_key = '_order_total'
																				AND p.post_type = 'shop_order_refund')
										WHERE (DATE(p.post_date) BETWEEN %s AND %s
													OR DATE(p.post_date) BETWEEN %s AND %s)
												AND p.post_parent NOT IN (SELECT id from {$wpdb->prefix}posts WHERE post_type = 'shop_order' AND post_status = 'wc-refunded')
										GROUP BY order_date, period", $cumm_dates['cp_start_date'], $cumm_dates['lp_start_date'], $cumm_dates['format'],
															$cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
															$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

			$results_partial_refund = $wpdb->get_results( $query , 'ARRAY_A');

			$tot_partial_refunds = array('LP' => array(), 'CP' => array());

			if ( count($results_partial_refund) > 0 ) {
				foreach ( $results_partial_refund as $row ) {
					$tot_partial_refunds[$row['period']][$row['order_date']] = array( 'tot' => $row['tot'], 'refunds' => $row['refunds'] );
				}
			}

			if ( count($results) > 0 ) {
				foreach ( $results as $row ) {

					$rtot = (!empty($tot_partial_refunds[$row['period']][$row['order_date']]['tot'])) ? $tot_partial_refunds[$row['period']][$row['order_date']]['tot'] : 0;
					$partial_refund = (!empty($tot_partial_refunds[$row['period']][$row['order_date']]['refunds'])) ? $tot_partial_refunds[$row['period']][$row['order_date']]['refunds'] : 0;

					if( $row['period'] == 'CP' ){

						if (!array_key_exists($row['order_date'], $p2i) ) {
							error_log('WooCommerce Subscription Reporting: Invalid value for "order_date" in DB results - '.$row['order_date']);
							continue;
						}

						// Index of this period - this will be used to position different chart data at this period's index
						$i = $p2i[ $row['order_date'] ];

						//KPI Data
						$this->cumm_kpi_data['kpi'] ['total_revenue'] ['cp'] += ($row['tot'] + $rtot);
						$this->cumm_kpi_data['kpi'] ['refunds'] ['cp'] += ($row['refunds'] + $partial_refund);

						//Chart Data
						$this->cumm_kpi_data['chart'] ['total_revenue'][ $i ] += round ( ($row['tot'] + $rtot), $this->req_params ['decimal_places'] );
						$this->cumm_kpi_data['chart'] ['refunds'][ $i ] += round ( ($row['refunds'] + $partial_refund), $this->req_params ['decimal_places'] );

					} else if( $row['period'] == 'LP' ) {
						$this->cumm_kpi_data['kpi'] ['total_revenue'] ['lp'] += ($row['tot'] + $rtot);
						$this->cumm_kpi_data['kpi'] ['refunds'] ['lp'] += ($row['refunds'] + $partial_refund);
					}
				}

			}

			// ###############################
			// MRR Contribution To Revenue
			// ###############################

			$this->cumm_kpi_data['kpi'] ['MRR_contribution_to_revenue'] ['cp'] = (!empty($this->cumm_kpi_data['kpi'] ['total_revenue'] ['cp'])) ? ($this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp_tot']/$this->cumm_kpi_data['kpi'] ['total_revenue'] ['cp'])*100 : $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp_tot'];
			$this->cumm_kpi_data['kpi'] ['MRR_contribution_to_revenue'] ['lp'] = (!empty($this->cumm_kpi_data['kpi'] ['total_revenue'] ['lp'])) ? ($this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp_tot']/$this->cumm_kpi_data['kpi'] ['total_revenue'] ['lp'])*100 : $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp_tot'];

			//Chart data
			foreach ($this->cumm_kpi_data['chart']['period'] as $key => $value) {
				$this->cumm_kpi_data['chart'] ['MRR_contribution_to_revenue'] [$key] = (!empty($this->cumm_kpi_data['chart'] ['total_revenue'] [$key])) ? ($this->cumm_kpi_data['chart'] ['monthly_recurring_revenue_(MRR)'] [$key]/$this->cumm_kpi_data['chart'] ['total_revenue'] [$key])*100 : $this->cumm_kpi_data['chart'] ['monthly_recurring_revenue_(MRR)'] [$key];	
				$this->cumm_kpi_data['chart'] ['MRR_contribution_to_revenue'] [$key] = round ( $this->cumm_kpi_data['chart'] ['MRR_contribution_to_revenue'] [$key], $this->req_params ['decimal_places'] );
			}

			// ###############################
			// User Churn Rate
			// ###############################

			// Get the total customer count for CP and LP
			$query = $wpdb->prepare( "SELECT CASE 
												WHEN DATE(p.post_date) <= %s THEN 'lp_start' 
												WHEN DATE(p.post_date) <= %s THEN 'lp_end' 
												WHEN DATE(p.post_date) <= %s THEN 'cp_start' 
												WHEN DATE(p.post_date) <= %s THEN 'cp_end' 
											END AS period,
											IFNULL( COUNT( DISTINCT( CASE 
																		WHEN pm1.meta_value > 0 THEN pm1.meta_value 
																		ELSE pm2.meta_value 
																	END ) ), 0) AS customers
										FROM {$wpdb->prefix}posts AS p
											JOIN {$wpdb->prefix}postmeta AS pm1 ON (pm1.post_id = p.id 
																					AND pm1.meta_key = '_customer_user' 
																					AND p.post_type = 'shop_order'
																					AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold'))
											JOIN {$wpdb->prefix}postmeta AS pm2 ON (pm2.post_id = pm1.post_id 
																					AND pm2.meta_key = '_billing_email')
											JOIN {$wpdb->prefix}postmeta AS pm3 ON (pm3.post_id = pm2.post_id 
																					AND pm3.meta_key = '_order_total'
																					AND pm3.meta_value > 0)
										WHERE DATE(p.post_date) <= '%s'
										GROUP BY period", $cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'], 
																	$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date'],
																	$cumm_dates['cp_end_date']);

			$results = $wpdb->get_results($query, 'ARRAY_A');

			if ( count($results) > 0 ) {

				$cust_count = array('cp_start' => 0, 'cp_end' => 0, 'lp_start' => 0, 'lp_end' => 0);

				foreach ( $results as $row ) {

					switch ( $row['period'] ) {
						case 'cp_start':
					        $cust_count['cp_start'] += $row['customers'];
					        break;

					    case 'cp_end':
					        $cust_count['cp_end'] += $row['customers'];
					        break;

					    case 'lp_start':
					        $cust_count['lp_start'] += $row['customers'];
					        break;

					    case 'lp_end':
					        $cust_count['lp_end'] += $row['customers'];
					        break;
					}
				}
				
				// Get the Chart Data for churn rate
				$query = $wpdb->prepare( "SELECT concat(DATE_FORMAT(p.post_date, '%s'), '".$time_str."') AS order_date,
												IFNULL( COUNT( DISTINCT( CASE 
																			WHEN pm1.meta_value > 0 THEN pm1.meta_value 
																			ELSE pm2.meta_value 
																		END ) ), 0) AS customers
											FROM {$wpdb->prefix}posts AS p
												JOIN {$wpdb->prefix}postmeta AS pm1 ON (pm1.post_id = p.id 
																						AND pm1.meta_key = '_customer_user' 
																						AND p.post_type = 'shop_order'
																						AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold'))
												JOIN {$wpdb->prefix}postmeta AS pm2 ON (pm2.post_id = pm1.post_id 
																						AND pm2.meta_key = '_billing_email')
											WHERE DATE(p.post_date) BETWEEN '%s' AND '%s'
											GROUP BY order_date", $cumm_dates['format'], $cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

				$results = $wpdb->get_results($query, 'ARRAY_A');

				if ( count($results) > 0 ) {
					foreach ( $results as $row ) {

						//For Chart Data
						if (!array_key_exists($row['order_date'], $p2i) ) {
							error_log('WooCommerce Subscription Reporting: Invalid value for "order_date" in DB results - '.$row['order_date']);
							continue;
						}
						
						$i = $p2i[ $row['order_date'] ];// Index of this period - this will be used to position different chart data at this period's index
						$this->cumm_kpi_data['chart'] ['user_churn_rate'][ $i ] += $row['customers'];
					}
				}


				//total customers in the period
				// $cust_count['cp_tot'] = $cust_count['cp_start'] + $cust_count['cp_end'];
				// $cust_count['lp_tot'] = $cust_count['lp_start'] + $cust_count['lp_end'];

				//actual customers at the end of the period
				$cust_count['lp_end'] = $cust_count['lp_end'] + $cust_count['lp_start'];
				$cust_count['cp_start'] = $cust_count['cp_start'] + $cust_count['lp_end'];
				$cust_count['cp_end'] = $cust_count['cp_end'] + $cust_count['cp_start'];

				//new customers added in the period
				$cust_count['cp_net'] = $cust_count['cp_end'] - $cust_count['cp_start'];
				$cust_count['lp_net'] = $cust_count['lp_end'] - $cust_count['lp_start'];

				$cust_count['cp_tot'] = $cust_count['cp_end'];
				$cust_count['lp_tot'] = $cust_count['lp_end'];

				//number of customers in days
				$cust_count['cp_cust_days'] = ($cust_count['cp_start'] * $cumm_dates['cp_diff_dates']) + ($cust_count['cp_net'] * $cumm_dates['cp_diff_dates']);
				$cust_count['lp_cust_days'] = ($cust_count['lp_start'] * $cumm_dates['lp_diff_dates']) + ($cust_count['lp_net'] * $cumm_dates['lp_diff_dates']);

				//calculating the churn rate for the period
				$this->cumm_kpi_data['kpi'] ['user_churn_rate'] ['cp'] = (!empty($cust_count['cp_cust_days']) ? $cust_count['cp_net']/$cust_count['cp_cust_days'] : $cust_count['cp_net']) * $cumm_dates['cp_diff_dates'] *100;
				$this->cumm_kpi_data['kpi'] ['user_churn_rate'] ['lp'] = (!empty($cust_count['lp_cust_days']) ? $cust_count['lp_net']/$cust_count['lp_cust_days'] : $cust_count['lp_net']) * $cumm_dates['lp_diff_dates'] *100;

				//calculating the diff_dates

				$cust_diff_dates = 31;

				if ($cumm_dates['tick_format'] == "%#d/%b/%Y") {
			        $cust_diff_dates = 1;
			    } else if ($cumm_dates['tick_format'] == "%b") {
			        $cust_diff_dates = 31;
			    } else if ($cumm_dates['tick_format'] == "%Y") {
			        $cust_diff_dates = 365;
			    } else {
			        $cust_diff_dates = 0.0417;
			    }

				//Chart data
				$cust_start_count = $cust_count['cp_start']; //initial start base
				foreach ($this->cumm_kpi_data['chart']['period'] as $key => $value) {

					$cp_end = $this->cumm_kpi_data['chart']['user_churn_rate'][$key]; //total customer for the period

					$cust_end_count = $this->cumm_kpi_data['chart']['user_churn_rate'][$key] + $cust_start_count; //calc end count
					$cust_net_count = $cust_end_count - $cust_start_count; //new customers added in the period
					$cust_days = ($cust_start_count * $cust_diff_dates) + ($cust_net_count * $cust_diff_dates); //number of customers in days					
					$this->cumm_kpi_data['chart']['user_churn_rate'][$key] = (!empty($cust_days) ? $cust_net_count/$cust_days : $cust_net_count) * $cust_diff_dates *100; //calculating the churn rate
					$cust_start_count += $cp_end; //adding to the base

					$this->cumm_kpi_data['chart']['user_churn_rate'][$key] = round ( $this->cumm_kpi_data['chart']['user_churn_rate'][$key], $this->req_params ['decimal_places'] );

					//ARPU
					$this->cumm_kpi_data['chart'] ['avg._revenue_per_paid_user_(ARPPU)'] [$key] = (!empty($cust_end_count)) ? $this->cumm_kpi_data['chart'] ['total_revenue'] [$key]/$cust_end_count : $this->cumm_kpi_data['chart'] ['total_revenue'] [$key]; 
					$this->cumm_kpi_data['chart'] ['avg._revenue_per_paid_user_(ARPPU)'] [$key] = round ( $this->cumm_kpi_data['chart'] ['avg._revenue_per_paid_user_(ARPPU)'] [$key], $this->req_params ['decimal_places'] );

					//LTV
					$this->cumm_kpi_data['chart'] ['life_time_value_(LTV)'] [$key] = (!empty($this->cumm_kpi_data['chart'] ['user_churn_rate'] [$key])) ? $this->cumm_kpi_data['chart'] ['avg._revenue_per_paid_user_(ARPPU)'] [$key]/($this->cumm_kpi_data['chart'] ['user_churn_rate'] [$key] / 100) : $this->cumm_kpi_data['chart'] ['avg._revenue_per_paid_user_(ARPPU)'] [$key];
					$this->cumm_kpi_data['chart'] ['life_time_value_(LTV)'] [$key] = round ( $this->cumm_kpi_data['chart'] ['life_time_value_(LTV)'] [$key], $this->req_params ['decimal_places'] );

					//Annual Run Rate
					$this->cumm_kpi_data['chart'] ['annual_run_rate'] [$key] = (!empty($this->cumm_kpi_data['chart'] ['monthly_recurring_revenue_(MRR)'] [$key])) ? $this->cumm_kpi_data['chart'] ['monthly_recurring_revenue_(MRR)'] [$key]*12 : 0;
					$this->cumm_kpi_data['chart'] ['annual_run_rate'] [$key] = round ( $this->cumm_kpi_data['chart'] ['annual_run_rate'] [$key], $this->req_params ['decimal_places'] );
				}

				// ###############################
				// Avg. Revenue Per User (ARPPU)
				// ###############################

				$this->cumm_kpi_data['kpi'] ['avg._revenue_per_paid_user_(ARPPU)'] ['cp'] = (!empty($cust_count['cp_tot'])) ? $this->cumm_kpi_data['kpi'] ['total_revenue'] ['cp']/$cust_count['cp_tot'] : $this->cumm_kpi_data['kpi'] ['total_revenue'] ['cp'];
				$this->cumm_kpi_data['kpi'] ['avg._revenue_per_paid_user_(ARPPU)'] ['lp'] = (!empty($cust_count['lp_tot'])) ? $this->cumm_kpi_data['kpi'] ['total_revenue'] ['lp']/$cust_count['lp_tot'] : $this->cumm_kpi_data['kpi'] ['total_revenue'] ['lp'];


				// ###############################
				// LifeTime Value (LTV)
				// ###############################

				$this->cumm_kpi_data['kpi'] ['life_time_value_(LTV)'] ['cp'] = (!empty($this->cumm_kpi_data['kpi'] ['user_churn_rate'] ['cp'])) ? $this->cumm_kpi_data['kpi'] ['avg._revenue_per_paid_user_(ARPPU)'] ['cp']/($this->cumm_kpi_data['kpi'] ['user_churn_rate'] ['cp']/100) : $this->cumm_kpi_data['kpi'] ['avg._revenue_per_paid_user_(ARPPU)'] ['cp'];
				$this->cumm_kpi_data['kpi'] ['life_time_value_(LTV)'] ['lp'] = (!empty($this->cumm_kpi_data['kpi'] ['user_churn_rate'] ['lp'])) ? $this->cumm_kpi_data['kpi'] ['avg._revenue_per_paid_user_(ARPPU)'] ['lp']/($this->cumm_kpi_data['kpi'] ['user_churn_rate'] ['lp']/100) : $this->cumm_kpi_data['kpi'] ['avg._revenue_per_paid_user_(ARPPU)'] ['lp'];


				// ###############################
				// Annual Run Rate
				// ###############################

				$this->cumm_kpi_data['kpi'] ['annual_run_rate'] ['cp'] = (!empty($this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp'])) ? $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['cp']*12 : 0;
				$this->cumm_kpi_data['kpi'] ['annual_run_rate'] ['lp'] = (!empty($this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp'])) ? $this->cumm_kpi_data['kpi'] ['monthly_recurring_revenue_(MRR)'] ['lp']*12 : 0;


				// ###############################
				// Upgrades and Downgrades
				// ###############################

				$order_date_cond1 = (!empty($time_str)) ? " ,concat(DATE_FORMAT(wsr1.order_time, '%s'), '".$time_str."') AS order_date " : " ,concat(DATE_FORMAT(wsr1.order_date, '%s'), '".$time_str."') AS order_date ";

				$query = $wpdb->prepare( "SELECT CASE 
													WHEN wsr1.order_date >= %s THEN 'CP' 
													WHEN wsr1.order_date >= %s THEN 'LP'
												END AS period,
												IFNULL(SUM( CASE WHEN (wsr2.price - wsr1.price) > 0 THEN 1 END), 0) AS upgrade_count,
												IFNULL(SUM( CASE WHEN (wsr2.price - wsr1.price) < 0 THEN 1 END), 0) AS downgrade_count,
												IFNULL(SUM( CASE WHEN (wsr2.price - wsr1.price) > 0 THEN (wsr2.price - wsr1.price) END), 0) AS upgrade_rev,
												IFNULL(SUM( CASE WHEN (wsr2.price - wsr1.price) < 0 THEN (wsr2.price - wsr1.price)*-1 END), 0) AS downgrade_rev
												$order_date_cond1
											FROM {$wpdb->prefix}wsr_orders AS wsr1
												JOIN {$wpdb->prefix}wsr_orders AS wsr2 ON ( wsr2.sub_id = wsr1.sub_id
																						AND wsr2.p_id = wsr1.p_id
																						AND wsr2.type = wsr1.type
																						AND wsr2.sub_status = wsr1.sub_status
																						AND wsr1.type =  'shop_order'
																						AND wsr1.sub_status =  'wc-active'
																						AND wsr1.v_id >0
																						AND wsr2.v_id >0
																						AND wsr2.trash = wsr1.trash
																						AND wsr1.trash = 0
																						AND wsr1.order_id = wsr2.sub_switch )
											WHERE (wsr1.order_date BETWEEN %s AND %s OR wsr1.order_date BETWEEN %s AND %s)
											GROUP BY order_date, period", $cumm_dates['cp_start_date'], $cumm_dates['lp_start_date'], $cumm_dates['format'],
															$cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
															$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

				$results = $wpdb->get_results($query, 'ARRAY_A');

				if ( count($results) > 0 ) {
					foreach ( $results as $row ) {
						if( $row['period'] == 'CP' ){

							if (!array_key_exists($row['order_date'], $p2i) ) {
								error_log('WooCommerce Subscription Reporting: Invalid value for "order_date" in DB results - '.$row['order_date']);
								continue;
							}

							// Index of this period - this will be used to position different chart data at this period's index
							$i = $p2i[ $row['order_date'] ];

							//KPI Data
							$this->cumm_kpi_data['kpi'] ['no._of_upgrades'] ['cp'] += $row['upgrade_count'];
							$this->cumm_kpi_data['kpi'] ['no._of_downgrades'] ['cp'] += $row['downgrade_count'];
							$this->cumm_kpi_data['kpi'] ['upgrade_revenue'] ['cp'] += $row['upgrade_rev'];
							$this->cumm_kpi_data['kpi'] ['downgrade_revenue'] ['cp'] += $row['downgrade_rev'];

							//Chart Data
							$this->cumm_kpi_data['chart'] ['no._of_upgrades'] [$i] += round ( $row['upgrade_count'], $this->req_params ['decimal_places'] );
							$this->cumm_kpi_data['chart'] ['no._of_downgrades'] [$i] += round ( $row['downgrade_count'], $this->req_params ['decimal_places'] );
							$this->cumm_kpi_data['chart'] ['upgrade_revenue'] [$i] += round ( $row['upgrade_rev'], $this->req_params ['decimal_places'] );
							$this->cumm_kpi_data['chart'] ['downgrade_revenue'] [$i] += round ( $row['downgrade_rev'], $this->req_params ['decimal_places'] );

						} else if( $row['period'] == 'LP' ){
							$this->cumm_kpi_data['kpi'] ['no._of_upgrades'] ['lp'] += $row['upgrade_count'];
							$this->cumm_kpi_data['kpi'] ['no._of_downgrades'] ['lp'] += $row['downgrade_count'];
							$this->cumm_kpi_data['kpi'] ['upgrade_revenue'] ['lp'] += $row['upgrade_rev'];
							$this->cumm_kpi_data['kpi'] ['downgrade_revenue'] ['lp'] += $row['downgrade_rev'];
						}
					}
				}

			}

			// ###############################
			// Sub statuses
			// ###############################

			// query to get sub status and trials and paid counts

			$query = $wpdb->prepare( "SELECT  CASE 
													WHEN order_date >= %s THEN 'CP' 
													WHEN order_date >= %s THEN 'LP'
											  END AS period,
											IFNULL(COUNT( DISTINCT(CASE WHEN sub_status = 'wc-active' THEN sub_id END)), 0) AS active,
											IFNULL(COUNT( DISTINCT(CASE WHEN sub_status IN ('wc-suspend', 'wc-on-hold') THEN sub_id END)), 0) AS suspended,
											IFNULL(COUNT( DISTINCT(CASE WHEN sub_status IN ('wc-cancelled', 'wc-pending-cancel', 'wc-expired') THEN sub_id END)), 0) AS cancelled,
											
											IFNULL(COUNT( DISTINCT(CASE WHEN sub_status = 'wc-active' AND (
												sub_trial_end >= %s ) THEN sub_id END)), 0) AS cp_trials,
											IFNULL(COUNT( DISTINCT(CASE WHEN sub_status = 'wc-active' AND
												sub_trial_end < %s AND (order_date BETWEEN %s AND %s) AND total > 0 THEN sub_id END)), 0) AS cp_paid,
											IFNULL(COUNT( DISTINCT(CASE WHEN sub_status = 'wc-active' AND
												sub_trial_end < %s AND (order_date BETWEEN %s AND %s) AND sub_trial_end != '0000-00-00' AND total > 0 THEN sub_id END)), 0) AS cp_trial_to_paid,

											IFNULL(COUNT( DISTINCT(CASE WHEN sub_status = 'wc-active' AND (
												sub_trial_end >= %s ) AND (order_date BETWEEN %s AND %s) THEN sub_id END)), 0) AS lp_trials,
											IFNULL(COUNT( DISTINCT(CASE WHEN sub_status = 'wc-active' AND
												sub_trial_end < %s AND (order_date BETWEEN %s AND %s) AND total > 0 THEN sub_id END)), 0) AS lp_paid,
											IFNULL(COUNT( DISTINCT(CASE WHEN sub_status = 'wc-active' AND
												sub_trial_end < %s AND (order_date BETWEEN %s AND %s) AND sub_trial_end != '0000-00-00' AND total > 0 THEN sub_id END)), 0) AS lp_trial_to_paid
											$order_date_cond
									FROM {$wpdb->prefix}wsr_orders
									WHERE type = 'shop_order'
										AND is_renewal = 0
										AND sub_switch = 0
										AND trash = 0
										AND (order_date BETWEEN %s AND %s OR order_date BETWEEN %s AND %s)
									GROUP BY order_date, period", $cumm_dates['cp_start_date'], $cumm_dates['lp_start_date'],
														$cumm_dates['cp_end_date'], $cumm_dates['cp_end_date'],
														$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date'],
														$cumm_dates['cp_end_date'], $cumm_dates['cp_start_date'], $cumm_dates['cp_end_date'],
														$cumm_dates['lp_end_date'], $cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
														$cumm_dates['lp_end_date'], $cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
														$cumm_dates['lp_end_date'], $cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
														$cumm_dates['format'], $cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
														$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

			
			$results = $wpdb->get_results( $query , 'ARRAY_A');

			if ( count($results) > 0 ) {
				foreach ( $results as $row ) {
					if( $row['period'] == 'CP' ){
						
						if (!array_key_exists($row['order_date'], $p2i) ) {
							error_log('WooCommerce Subscription Reporting: Invalid value for "order_date" in DB results - '.$row['order_date']);
							continue;
						}

						// Index of this period - this will be used to position different chart data at this period's index
						$i = $p2i[ $row['order_date'] ];

						//KPI Data
						$this->cumm_kpi_data['kpi'] ['active'] ['cp'] += $row['active'];
						$this->cumm_kpi_data['kpi'] ['suspended_/_on_hold'] ['cp'] += $row['suspended'];
						$this->cumm_kpi_data['kpi'] ['cancelled_/_expired'] ['cp'] += $row['cancelled'];
						$this->cumm_kpi_data['kpi'] ['trials'] ['cp'] += $row['cp_trials'];
						$this->cumm_kpi_data['kpi'] ['paid'] ['cp'] += $row['cp_paid'];
						$this->cumm_kpi_data['kpi'] ['trials_to_paid'] ['cp'] += $row['cp_trial_to_paid'];

						//Chart Data
						$this->cumm_kpi_data['chart'] ['active'] [$i] += round ( $row['active'], $this->req_params ['decimal_places'] );
						$this->cumm_kpi_data['chart'] ['suspended_/_on_hold'] [$i] += round ( $row['suspended'], $this->req_params ['decimal_places'] );
						$this->cumm_kpi_data['chart'] ['cancelled_/_expired'] [$i] += round ( $row['cancelled'], $this->req_params ['decimal_places'] );
						$this->cumm_kpi_data['chart'] ['trials'] [$i] += round ( $row['cp_trials'], $this->req_params ['decimal_places'] );
						$this->cumm_kpi_data['chart'] ['paid'] [$i] += round ( $row['cp_paid'], $this->req_params ['decimal_places'] );
						$this->cumm_kpi_data['chart'] ['trials_to_paid'] [$i] += round ( $row['cp_trial_to_paid'], $this->req_params ['decimal_places'] );

					} else if( $row['period'] == 'LP' ){
						$this->cumm_kpi_data['kpi'] ['active'] ['lp'] += $row['active'];
						$this->cumm_kpi_data['kpi'] ['suspended_/_on_hold'] ['lp'] += $row['suspended'];
						$this->cumm_kpi_data['kpi'] ['cancelled_/_expired'] ['lp'] += $row['cancelled'];
						$this->cumm_kpi_data['kpi'] ['trials'] ['lp'] += $row['lp_trials'];
						$this->cumm_kpi_data['kpi'] ['paid'] ['lp'] += $row['lp_paid'];
						$this->cumm_kpi_data['kpi'] ['trials_to_paid'] ['lp'] += $row['lp_trial_to_paid'];
					}
				}
			}


			// ###############################
			// Switched Subscriptions
			// ###############################

			// Get switched subscriptions count
			$query = $wpdb->prepare( "SELECT CASE 
												WHEN order_date >= %s THEN 'CP' 
												WHEN order_date >= %s THEN 'LP'
										  END AS period,
										IFNULL(COUNT( DISTINCT(CASE WHEN sub_switch > 0 THEN sub_id END)), 0) AS switched
										$order_date_cond
									  FROM {$wpdb->prefix}wsr_orders
										WHERE type = 'shop_order'
											AND trash = 0
											AND (order_date BETWEEN %s AND %s OR order_date BETWEEN %s AND %s)
									  GROUP BY order_date, period", $cumm_dates['cp_start_date'], $cumm_dates['lp_start_date'], $cumm_dates['format'],
																	$cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
																	$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

			$results = $wpdb->get_results($query, 'ARRAY_A');

			if ( count($results) > 0 ) {
				foreach ( $results as $row ) {
					if( $row['period'] == 'CP' ){
						
						if (!array_key_exists($row['order_date'], $p2i) ) {
							error_log('WooCommerce Subscription Reporting: Invalid value for "order_date" in DB results - '.$row['order_date']);
							continue;
						}

						// Index of this period - this will be used to position different chart data at this period's index
						$i = $p2i[ $row['order_date'] ];

						$this->cumm_kpi_data['kpi'] ['switched'] ['cp'] += $row['switched']; //KPI Data
						$this->cumm_kpi_data['chart'] ['switched'] [$i] += round ( $row['switched'], $this->req_params ['decimal_places'] ); //Chart Data

					} else if( $row['period'] == 'LP' ){
						$this->cumm_kpi_data['kpi'] ['switched'] ['lp'] += $row['switched'];
					}
				}

			}

			// ###############################
			// New Signups
			// ###############################

			// Get minimum user id for people registered yesterday and today
			$query = $wpdb->prepare( "SELECT CASE 
												WHEN DATE(user_registered) >= %s THEN 'CP' 
												WHEN DATE(user_registered) >= %s THEN 'LP'
											END AS period,
											IFNULL(MIN(ID), -1) as min_user_id
									  FROM {$wpdb->prefix}users
									  WHERE (DATE(user_registered) BETWEEN %s AND %s OR DATE(user_registered) BETWEEN %s AND %s)
									  GROUP BY period", $cumm_dates['cp_start_date'], $cumm_dates['lp_start_date'],
														$cumm_dates['lp_start_date'], $cumm_dates['lp_end_date'],
														$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

			$results = $wpdb->get_results($query, 'ARRAY_A');

			$cp_cust_cond = $lp_cust_cond = '';

			if ( count($results) > 0 ) {
				foreach ( $results as $row ) {
					if( $row['period'] == 'CP' ){
						$cp_cust_cond = (!empty($row['min_user_id'])) ? " OR user_id >= ".$row['min_user_id'] : '';
					} else if( $row['period'] == 'LP' ){
						$lp_cust_cond = (!empty($row['min_user_id'])) ? " OR user_id >= ".$row['min_user_id'] : '';
					}
				}

			}

			// Get number of customers - guests are all considered new customers, but registered users need to have id greater than the min user id
			$query = $wpdb->prepare( "SELECT  CASE 
													WHEN order_date >= %s THEN 'CP' 
													WHEN order_date >= %s THEN 'LP'
												END AS period,
											IFNULL(COUNT( DISTINCT( CASE WHEN user_id > 0 THEN user_id ELSE cust_email END ) ),0) as cust
											$order_date_cond
										FROM {$wpdb->prefix}wsr_orders
										WHERE ( ( (order_date BETWEEN %s AND %s) AND (user_id = 0 ". $cp_cust_cond ."))
													OR ( (order_date BETWEEN %s AND %s) AND (user_id = 0 ". $lp_cust_cond .")) )
											AND sub_status = 'wc-active'
											AND type = 'shop_order'
											AND trash = 0
										GROUP BY order_date, period", $cumm_dates['cp_start_date'], $cumm_dates['lp_start_date'], $cumm_dates['format'],
															$cumm_dates['cp_start_date'], $cumm_dates['cp_end_date'],
															$cumm_dates['lp_start_date'], $cumm_dates['lp_end_date']);

			$results = $wpdb->get_results($query, 'ARRAY_A');

			if ( count($results) > 0 ) {
				foreach ( $results as $row ) {
					if( $row['period'] == 'CP' ){

						if (!array_key_exists($row['order_date'], $p2i) ) {
							error_log('WooCommerce Subscription Reporting: Invalid value for "order_date" in DB results - '.$row['order_date']);
							continue;
						}

						// Index of this period - this will be used to position different chart data at this period's index
						$i = $p2i[ $row['order_date'] ];

						//KPI Data
						$this->cumm_kpi_data['kpi'] ['new_signups'] ['cp'] += $row['cust'];

						//Chart Data
						$this->cumm_kpi_data['chart'] ['new_signups'] [$i] += round ( $row['cust'], $this->req_params ['decimal_places'] );

					} else if( $row['period'] == 'LP' ){
						$this->cumm_kpi_data['kpi'] ['new_signups'] ['lp'] += $row['cust'];
					}
				}
			}

			$this->format_kpi_data();

			// ###############################
			// Top 20 Products
			// ###############################

			$query = $wpdb->prepare ("SELECT p_id, 
											v_id,
										   IFNULL(SUM( total ),0) as sales,
										   IFNULL(SUM( qty ),0) as qty,
										   IFNULL(price ,0) as price,
										   sub_billing_period AS billing_period,
										   sub_billing_interval AS billing_interval
									FROM {$wpdb->prefix}wsr_orders
									WHERE order_date BETWEEN %s AND %s
										AND order_status IN ('wc-on-hold','wc-processing','wc-completed')
										AND trash = 0
									GROUP BY p_id, v_id
									ORDER BY CASE WHEN (SUM(total) > 0) THEN SUM(total) ELSE 0 END DESC, qty DESC
									LIMIT 20", $cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

			$results = $wpdb->get_results($query, 'ARRAY_A');

			$t_p_ids = $t_v_ids = array();

			$sub_interval = (function_exists('wcs_get_subscription_period_interval_strings')) ? wcs_get_subscription_period_interval_strings() : array('1' => '',
																																					    '2' => '2nd',
																																					    '3' => '3rd',
																																					    '4' => '4th',
																																					    '5' => '5th',
																																					    '6' => '6th');
			$sub_period = array( 'day' => 'd',
							    'week' => 'wk',
							    'month' => 'mo',
							    'year' => 'yr' );

			$sub_interval = array_map(function($str) {
					        		return str_replace('every ', '', $str);
					    		},
					    	$sub_interval
						);

			$sub_interval[1] = '';

			if ( count($results) > 0 ) {

				$prod_cond = '';

				$tot_cust = 0;

				foreach ( $results as $row ) {
					$id = $row['p_id'];

					$t_p_ids [] = $id;

					if (!empty($row['v_id'])) {
						$t_v_ids [$row['v_id']] = $row['p_id'];
						$id = $row['v_id'];
					}

					$tot_cust += $row['qty'];

					$this->cumm_kpi_data['kpi']['top_sub'][$id] = array( 'title' => '-',
																	'sales' => $row['sales'], 
																	'qty' => $row['qty'],
																	'price' => $row['price'],
																	'mrr' => 0,
																	'price_format' => ((!empty($sub_interval[$row['billing_interval']])) ? $sub_interval[$row['billing_interval']] . ' ' . $sub_period[$row['billing_period']] : $sub_period[$row['billing_period']]),
																	'cp_start' => 0,
																	'cp_end' => 0);
				}

				$t_p_ids = (!empty($t_p_ids)) ? array_unique($t_p_ids) : array();

				$prod_cond = (!empty($t_p_ids)) ? ' AND ( (p_id IN ('. implode(",",$t_p_ids) .') AND v_id = 0)' : '';
				$prod_cond .= (!empty($t_v_ids)) ? ( (!empty($prod_cond)) ? ' OR ' : ' AND ( ' ) . 'v_id IN ('. implode(",",array_keys($t_v_ids)) .')' : '';
				$prod_cond .= (!empty($prod_cond)) ? ' ) ' : '';

				// Get the product titles
				$ids = array('t_p_ids' => $t_p_ids, 't_v_ids' => $t_v_ids);
				$this->cumm_kpi_data['kpi']['top_sub'] = $this->get_prod_title($this->cumm_kpi_data['kpi']['top_sub'], $ids);				

				$min_max_stats = array('min' => array('id' => array(), 'val' => array()), 'max' => array('id' => array(), 'val' => array()));
				$min_max_stats['min']['id'] = $min_max_stats['min']['val'] = $min_max_stats['max']['id'] = $min_max_stats['max']['val'] = array('churn' => 0, 'ltv' => 0, 'arpu' => 0);


				// Get the MRR for each top subscription
				$query = $wpdb->prepare( "SELECT CASE WHEN v_id > 0 THEN v_id
													ELSE p_id
												END AS prod_id,
												IFNULL(SUM( CASE WHEN order_status IN ('wc-on-hold','wc-processing','wc-completed')
										                 THEN total*sub_billing_interval_months END), 0) AS mrr
												$order_date_cond
											FROM {$wpdb->prefix}wsr_orders
											WHERE order_date BETWEEN '%s' AND '%s'
												AND trash = 0
												".$prod_cond."
											GROUP BY prod_id, order_date
											ORDER BY prod_id", $cumm_dates['format'], $cumm_dates['cp_start_date'], $cumm_dates['cp_end_date']);

				$results = $wpdb->get_results($query, 'ARRAY_A');

				$cp_mrr = $cp_mrr_count = array();

				if ( count($results) > 0 ) {
					foreach ( $results as $row ) {

						if (!array_key_exists($row['order_date'], $p2i) ) {
							continue; 
						}

						// Index of this period - this will be used to position different chart data at this period's index
						$i = $p2i[ $row['order_date'] ];

						//KPI Data
						if( !empty($p2i) && sizeof($p2i)-1 == $i ) {
							if( empty($cp_mrr[$row['prod_id']]) ) {
								$cp_mrr[$row['prod_id']] = 0;
							}
							$cp_mrr[$row['prod_id']] += $row['mrr'];
						}

						if( empty($cp_mrr_count[$row['prod_id']]) ) {
							$cp_mrr_count[$row['prod_id']] = 0;
						}
						$cp_mrr_count[$row['prod_id']]++;
						
						$this->cumm_kpi_data['kpi']['top_sub'][$row['prod_id']]['mrr'] += $row['mrr'];
					}
				}

				foreach ($cp_mrr as $id => $value) {
					if(!empty($value)) {
						$this->cumm_kpi_data['kpi']['top_sub'][$id]['mrr'] = $value;
					} else {
						$this->cumm_kpi_data['kpi']['top_sub'][$id]['mrr'] = (!empty($this->cumm_kpi_data['kpi']['top_sub'][$id]['mrr']) && !empty($cp_mrr_count[$id]) ) ? $this->cumm_kpi_data['kpi']['top_sub'][$id]['mrr']/$cp_months : $this->cumm_kpi_data['kpi']['top_sub'][$id]['mrr'];
					}
				}

				// Get the total customer count for CP and LP
				$query = $wpdb->prepare( "SELECT CASE 
													WHEN order_date <= %s THEN 'cp_start' 
													WHEN order_date <= %s THEN 'cp_end' 
												END AS period,

												CASE WHEN v_id > 0 THEN v_id
													ELSE p_id
												END AS prod_id,
												IFNULL( COUNT( DISTINCT( CASE 
																			WHEN user_id > 0 THEN user_id 
																			ELSE cust_email 
																		END ) ), 0) AS customers
											FROM {$wpdb->prefix}wsr_orders
											WHERE order_date <= '%s'
												AND trash = 0
												AND total > 0
												".$prod_cond."
											GROUP BY prod_id, period
											ORDER BY prod_id", $cumm_dates['cp_start_date'], $cumm_dates['cp_end_date'],
																$cumm_dates['cp_end_date']);

				$results = $wpdb->get_results($query, 'ARRAY_A');

				foreach ( $results as $row ) {

					if ( !isset($this->cumm_kpi_data['kpi']['top_sub'][$row['prod_id']]) ) {
						continue;			
					}

					switch ( $row['period'] ) {
						case 'cp_start':
							$this->cumm_kpi_data['kpi']['top_sub'][$row['prod_id']]['cp_start'] = (!empty($row['customers'])) ? $row['customers'] : 0;
					        break;

					    case 'cp_end':
					        $this->cumm_kpi_data['kpi']['top_sub'][$row['prod_id']]['cp_end'] = (!empty($row['customers'])) ? $row['customers'] : 0;
					        break;
					}
				}

				// for finding the min & max for Churn, LTV & ARPU
				foreach ( $this->cumm_kpi_data['kpi']['top_sub'] as $key => &$row ) {

					$row['churn'] = $row['arpu'] = $row['ltv'] = 0;

					//actual customers at the end of the period
					$row['cp_end'] = $row['cp_end'] + $row['cp_start'];

					//total customers in the period
					$tot = $row['cp_end'];

					//new customers added in the period
					$net = $row['cp_end'] - $row['cp_start'];

					//number of customers in days
					$cust_days = ($row['cp_start'] * $cumm_dates['cp_diff_dates']) + ($net * $cumm_dates['cp_diff_dates']);

					//calculating the churn rate for the period
					$row['churn'] = (!empty($cust_days) ? $net/$cust_days : $net) * $cumm_dates['cp_diff_dates'] * 100;
					// $row['churn'] = (!empty($cust_days) ? $net/$cust_days : $net) * 100;

					// Avg. Revenue Per User (ARPU)					
					$row['arpu'] = (!empty($tot)) ? $row['sales']/$tot : $row['sales'];
					
					// LifeTime Value (LTV)
					$row['ltv'] = (!empty($row['churn'])) ? $row['arpu']/($row['churn'] / 100) : $row['arpu'];

					if($min_max_stats['min']['val']['churn'] > $row['churn'] || $min_max_stats['min']['val']['churn'] == 0) {
						$min_max_stats['min']['val']['churn'] = $row['churn'];
						$min_max_stats['min']['id']['churn'] = $key;
					}

					if($min_max_stats['max']['val']['churn'] < $row['churn'] || $min_max_stats['max']['val']['churn'] == 0) {
						$min_max_stats['max']['val']['churn'] = $row['churn'];
						$min_max_stats['max']['id']['churn'] = $key;
					}

					if($min_max_stats['min']['val']['ltv'] > $row['ltv'] || $min_max_stats['min']['val']['ltv'] == 0 ) {
						$min_max_stats['min']['val']['ltv'] = $row['ltv'];
						$min_max_stats['min']['id']['ltv'] = $key;
					}

					if($min_max_stats['max']['val']['ltv'] < $row['ltv'] || $min_max_stats['max']['val']['ltv'] == 0) {
						$min_max_stats['max']['val']['ltv'] = $row['ltv'];
						$min_max_stats['max']['id']['ltv'] = $key;
					}

					if($min_max_stats['min']['val']['arpu'] > $row['arpu'] || $min_max_stats['min']['val']['arpu'] == 0 ) {
						$min_max_stats['min']['val']['arpu'] = $row['arpu'];
						$min_max_stats['min']['id']['arpu'] = $key;
					} 

					if($min_max_stats['max']['val']['arpu'] < $row['arpu'] || $min_max_stats['max']['val']['arpu'] == 0) {
						$min_max_stats['max']['val']['arpu'] = $row['arpu'];
						$min_max_stats['max']['id']['arpu'] = $key;
					}
				}

				$html = '<tr>'.
							'<th style="text-align:left;">'. __('Plan', WSR_DOMAIN) .'</th>'.
							'<th style="width: 7em;">'. __('Customers', WSR_DOMAIN) .'</th>'.
							'<th>'. __('MRR', WSR_DOMAIN) .'</th>'.
							'<th style="width: 3em;">'. __('Churn Rate', WSR_DOMAIN) .'</th>'.
							'<th>'. __('LTV', WSR_DOMAIN) .'</th>'.
							'<th>'. __('ARPU', WSR_DOMAIN) .'</th>'.
							'<th>'. __('Sales', WSR_DOMAIN) .'</th>'.
						'</tr>';


				foreach ( $this->cumm_kpi_data['kpi']['top_sub'] as $key => &$row ) {

					//code for css classes for min & max for churn, ltv, arpu
					$churn_min_max = $ltv_min_max = $arpu_min_max = '';

					if( $min_max_stats['min']['id']['churn'] == $key ) {
						$churn_min_max = 'wsr_cell_green';
					}else if( $min_max_stats['max']['id']['churn'] == $key ) {
						$churn_min_max = 'wsr_cell_red';
					}

					if( $min_max_stats['min']['id']['ltv'] == $key ) {
						$ltv_min_max = 'wsr_cell_red';
					}else if( $min_max_stats['max']['id']['ltv'] == $key ) {
						$ltv_min_max = 'wsr_cell_green';
					}

					if( $min_max_stats['min']['id']['arpu'] == $key ) {
						$arpu_min_max = 'wsr_cell_red';
					}else if( $min_max_stats['max']['id']['arpu'] == $key ) {
						$arpu_min_max = 'wsr_cell_green';
					}

					// code for formatting and html

					$price = $row['price'];

					$cust_count = (!empty($tot_cust)) ? ($row['qty'] / $tot_cust) * 100 : $row['qty'];

					$row['sales'] = $this->req_params ['currency_symbol'] . number_format ( $row['sales'], $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] );
					$row['qty'] = number_format ( $row['qty'], 0, $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] );
					$cust_count = number_format ( $cust_count, $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] ) . '%';
					$row['price'] = $this->req_params ['currency_symbol'] . number_format ( $row['price'], $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] );
					$row['mrr'] = $this->req_params ['currency_symbol'] . number_format ( $row['mrr'], $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] );
					$row['churn'] = number_format ( $row['churn'], $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] ) . '%';
					$row['arpu'] = $this->req_params ['currency_symbol'] . number_format ( $row['arpu'], $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] );
					$row['ltv'] = $this->req_params ['currency_symbol'] . number_format ( $row['ltv'], $this->req_params ['decimal_places'], $this->req_params ['decimal_sep'], $this->req_params ['thousand_sep'] );

					$html .= '<tr>'.
								'<td style="text-align: left;">'. $row['title'] .' <span class=wsr_top_prod_price>'. ( ($price > 0) ? $row['price'] .'/'. $row['price_format'] : $this->req_params ['currency_symbol'] . '0' ) .'</span> </td>'.
								'<td>'. $row['qty'] .' ⦁ <span style="color:black;">'. $cust_count .'</span> </td>'.
								'<td>'. $row['mrr'] .'</td>'.
								'<td '. ((!empty($churn_min_max)) ? 'class="'. $churn_min_max .'"' : '') .'>'. $row['churn'] .'</td>'.
								'<td '. ((!empty($ltv_min_max)) ? 'class="'. $ltv_min_max .'"' : '') .'>'. $row['ltv'] .'</td>'.
								'<td '. ((!empty($arpu_min_max)) ? 'class="'. $arpu_min_max .'"' : '') .'>'. $row['arpu'] .'</td>'.
								'<td>'. $row['sales'] .'</td>'.
							'</tr>';
				}

				$this->cumm_kpi_data['kpi']['top_sub'] = $html;
			}

			return json_encode($this->cumm_kpi_data);
		}


		public function get_date_series($date_params) {

			$date_series = array();

			if ($date_params['diff_dates'] > 0 && $date_params['diff_dates'] <= 30) {

		        $date = $date_params['start_date'];
		        $date_series[0] = $date;
		        for ($i = 1;$i<=$date_params['diff_dates'];$i++ ) {
		        		$date = date ("Y-m-d", strtotime($date .' +1 day'));
		                $date_series[] = $date;
		        }
		    } else if ($date_params['diff_dates'] > 30 && $date_params['diff_dates'] <= 950) { //for 2.6 years
		     
		        $month_series = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

		        $start_year = date('Y', strtotime($date_params['start_date']));
		        $start_month = date('n', strtotime($date_params['start_date'])) -1;

		        $end_year = date('Y', strtotime($date_params['end_date']));
		        $end_month = date('n', strtotime($date_params['end_date'])) -1;

		        for($i = $start_year; $i <= $end_year; $i++) {

		        	$start_month_temp = $start_month;

		        	if( $i != $end_year ) {
		        		$end_month_temp = 11;
		        	} else {
		        		$end_month_temp = $end_month;
		        	}

		        	while( $start_month_temp <= $end_month_temp ) {
		        		$date_series[] = $month_series[$start_month_temp] . ' ' . $i;
		        		$start_month_temp++;
		        	}
		        }

		    } else if ($date_params['diff_dates'] > 950) {

		        $year_strt = substr($date_params['start_date'], 0,4);
		        $year_end = substr($date_params['end_date'], 0,4);

		        $year_tmp[0] = $year_strt;

		        for ($i = 1;$i<=($year_end - $year_strt);$i++ ) {
		             $year_tmp [$i] = $year_tmp [$i-1] + 1;          
		        }

		        for ($i = 0;$i<sizeof($year_tmp);$i++ ) {
		            $date_series[] = $year_tmp[$i];
		        }
		    } else {

		    	$date = $date_params['start_date'];

		        $date_series[0] = "00:00:00";
		        for ($i = 1;$i<24;$i++ ) {
		            $date = date ("H:i:s", strtotime($date .' +1 hours'));
		            $date_series[$i] = $date;
		        }
		    }

		    return $date_series;
		}

		// function to get the cumm stats
		public function get_cumm_stats() {

			$cumm_dates = $date_series = array();

		    $cumm_dates['cp_start_date'] 	= $this->req_params['start_date'];
		    $cumm_dates['cp_end_date'] 		= $this->req_params['end_date'];
		    $cumm_dates['cp_diff_dates'] 	= (strtotime($cumm_dates['cp_end_date']) - strtotime($cumm_dates['cp_start_date']))/(60*60*24);

		    if ($cumm_dates['cp_diff_dates'] > 0) {
		        $cumm_dates['lp_end_date'] = date('Y-m-d', strtotime($cumm_dates['cp_start_date'] .' -1 day'));
		        $cumm_dates['lp_start_date'] = date('Y-m-d', strtotime($cumm_dates['lp_end_date']) - ($cumm_dates['cp_diff_dates']*60*60*24));
		    }
		    else {
		        $cumm_dates['lp_end_date'] = $cumm_dates['lp_start_date'] = date('Y-m-d', strtotime($cumm_dates['cp_start_date'] .' -1 day'));
		    }

		    $cumm_dates['lp_diff_dates'] = (strtotime($cumm_dates['lp_end_date']) - strtotime($cumm_dates['lp_start_date']))/(60*60*24);

		    // ================================================================================================
		    // TODO: convert the jqplot code to chart.js
		    if ($cumm_dates['cp_diff_dates'] > 0 && $cumm_dates['cp_diff_dates'] <= 30) {
		        $cumm_dates['tick_format'] = "%#d/%b/%Y";
		        $cumm_dates ['format'] = '%Y-%m-%d';
		    } else if ($cumm_dates['cp_diff_dates'] > 30 && $cumm_dates['cp_diff_dates'] <= 950) {
		        $cumm_dates['tick_format'] = "%b";
		        $cumm_dates ['format'] = '%b %Y';
		    } else if ($cumm_dates['cp_diff_dates'] > 950) {
		        $cumm_dates['tick_format'] = "%Y";
		        $cumm_dates ['format'] = '%Y';
		    } else {
		        $cumm_dates['tick_format'] = "%H:%M:%S";
		        $cumm_dates ['format'] = '%H';
		    }
		    // ================================================================================================

		    $date_series['cp'] = $this->get_date_series(array( 'start_date' => $cumm_dates['cp_start_date'],
		    													'end_date' => $cumm_dates['cp_end_date'],
		    													'diff_dates' => $cumm_dates['cp_diff_dates']));

		    $date_series['lp'] = $this->get_date_series(array( 'start_date' => $cumm_dates['lp_start_date'],
		    													'end_date' => $cumm_dates['lp_end_date'],
		    													'diff_dates' => $cumm_dates['lp_diff_dates']));

		    echo $this->query_cumm_stats($cumm_dates, $date_series);
	    	exit;

		}

	}
}