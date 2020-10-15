<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Subscription_Reporting_Install' ) ) {
	class Subscription_Reporting_Install {
		public $req_params = array();

		private static $collate = '';

		function __construct() {
			
		}

		
		private static function create_tables() {
			global $wpdb;

			$wpdb->hide_errors();
			self::get_schema();

			$wpdb->query(" CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wsr_orders (
							  sub_id bigint(20) NOT NULL,
							  order_id bigint(20) NOT NULL,
							  p_id bigint(20) NOT NULL,
							  v_id bigint(20) NOT NULL DEFAULT '0',
							  order_date date NOT NULL default '0000-00-00',
							  order_time time NOT NULL default '00:00:00',
							  type enum('shop_order','shop_order_refund') NOT NULL DEFAULT 'shop_order',
							  order_status ENUM('wc-pending' ,'wc-processing' ,'wc-on-hold' ,'wc-completed' ,'wc-cancelled' ,'wc-refunded' ,'wc-failed'),
							  sub_status ENUM('wc-pending', 'wc-active', 'wc-on-hold', 'wc-cancelled', 'wc-switched', 'wc-expired', 'wc-pending-cancel'),
							  user_id bigint(20) NOT NULL DEFAULT '0',
							  cust_email varchar(200) NOT NULL,
							  cust_name varchar(200) NOT NULL,
							  qty int(10) unsigned NOT NULL default '0',
							  total decimal(11,2) NOT NULL default '0.00',
							  price decimal(11,2) NOT NULL default '0.00',
							  currency varchar(50) NOT NULL,
							  payment_method varchar(200) NOT NULL,
							  sub_billing_period varchar(100) NOT NULL,
							  sub_billing_interval bigint(20) NOT NULL,
							  sub_billing_interval_months decimal(9,4) NOT NULL,
							  sub_trial_end date NOT NULL default '0000-00-00',
							  sub_end date NOT NULL default '0000-00-00',
							  is_renewal BIT(1) NOT NULL default 0,
							  sub_switch bigint(20) NOT NULL default 0,
							  trash BIT(1) NOT NULL default 0,
							  meta_values longtext NOT NULL,
					  		  o_item_id bigint(20),
					  		  update_flag BIT(1) NOT NULL default 0
							) ".self::$collate);

			$wpdb->query(" CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wsr_meta_all (post_id bigint(20) , meta_key text , meta_value text) ");
		}

		/**
		 * Get Table schema
		 * @return string
		 */
		private static function get_schema() {
			global $wpdb;

			self::$collate = '';

			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty( $wpdb->charset ) ) {
					self::$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if ( ! empty( $wpdb->this->collate ) ) {
					self::$collate .= " COLLATE $wpdb->collate";
				}
			}

		}

		public static function sync() {

			$params = (!empty($_POST['params'])) ? $_POST['params'] : array();

			if ( ! wp_verify_nonce( $params['security'], 'wsr-security' ) ) {
	     		return;
	     	}

			global $wpdb;

			if ( !empty($_POST['part']) && $_POST['part'] == 1 ) {

				//Code for creating tables
				self::create_tables();

				$slimit = 0;

			} else {
				$slimit = (($_POST['part']-1)*100);
			}

			// ###############################
			// Queries for inserting into temp table
			// ###############################
			
			// empty temp tables
			$wpdb->query("DELETE FROM {$wpdb->prefix}wsr_meta_all");

			// query for inserting subscription and renewal orders
			$wpdb->query("INSERT INTO {$wpdb->prefix}wsr_orders (sub_id, order_id, order_date, order_time)
							SELECT id, 
									post_parent, 
									DATE(post_date) as date,
									TIME(post_date) as time
							FROM {$wpdb->prefix}posts
							WHERE post_type = 'shop_subscription'
								AND post_parent > 0
								AND post_status != 'trash'
							UNION
							SELECT pm.meta_value,
									pm.post_id,
									DATE(p.post_date) as date,
									TIME(p.post_date) as time
							FROM {$wpdb->prefix}postmeta AS pm
								JOIN {$wpdb->prefix}posts AS p ON (pm.post_id = p.id 
																	AND p.post_type = 'shop_order'
																	AND p.post_parent = 0)
							WHERE pm.meta_key IN ('_subscription_renewal', '_subscription_switch')
								AND (pm.meta_value != '' OR pm.meta_value IS NOT NULL)
								AND p.post_status != 'trash'
							LIMIT ". $slimit .", 100");

			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
							SET wsro.order_status = (SELECT post_status 
														FROM {$wpdb->prefix}posts
														WHERE id = wsro.order_id),
							 	wsro.sub_status = (SELECT post_status 
														FROM {$wpdb->prefix}posts
														WHERE id = wsro.sub_id)
							WHERE wsro.update_flag = 0");

			$o_ids = $wpdb->get_col("SELECT DISTINCT order_id FROM {$wpdb->prefix}wsr_orders WHERE update_flag = 0");
		
			$v = '';

			foreach ( $o_ids as $id ) {
				$v .= "( ".$id.", '_billing_email'),( ".$id.", '_billing_first_name'),( ".$id.", '_billing_last_name'),
							( ".$id.", '_customer_user'),( ".$id.", '_order_currency'),( ".$id.", '_payment_method'),
							( ".$id.", '_subscription_renewal'), ( ".$id.", '_subscription_switch'), ";
			}

			$wpdb->query("INSERT INTO {$wpdb->prefix}wsr_meta_all(post_id, meta_key) VALUES ". substr($v, 0, (strlen($v)-2)));

			// query for getting the order related postmeta
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
						SET wsro.meta_values = (SELECT GROUP_CONCAT( IFNULL(pm.meta_value,'-') ORDER BY temp.meta_key SEPARATOR ' #wsr# ') AS meta_values
											FROM {$wpdb->prefix}wsr_meta_all as temp 
												LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.meta_key = temp.meta_key AND pm.post_id = temp.post_id)
											WHERE temp.post_id = wsro.order_id)
						WHERE wsro.update_flag = 0");

			//Code for transposing the order related postmeta concated data
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
							JOIN (SELECT wsro1.order_id AS oid, 
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 1), ' #wsr# ', -1) AS billing_email,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 2), ' #wsr# ', -1) AS billing_first_name,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 3), ' #wsr# ', -1) AS billing_last_name,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 4), ' #wsr# ', -1) AS customer_user,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 5), ' #wsr# ', -1) AS order_currency,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 6), ' #wsr# ', -1) AS payment_method,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 7), ' #wsr# ', -1) AS renewal,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 8), ' #wsr# ', -1) AS switch
									FROM {$wpdb->prefix}wsr_orders AS wsro1
									WHERE wsro1.meta_values != '') AS temp ON (temp.oid = wsro.order_id)
							SET wsro.cust_email = temp.billing_email,
								wsro.cust_name = concat(temp.billing_first_name, temp.billing_last_name),
								wsro.user_id = temp.customer_user,
								wsro.currency = temp.order_currency,
								wsro.payment_method = temp.payment_method,
								wsro.is_renewal = CASE WHEN temp.renewal > 0 THEN 1 END,
								wsro.sub_switch = CASE WHEN wsro.sub_switch = 0 AND wsro.sub_id = temp.switch AND temp.switch > 0 THEN 1 ELSE wsro.sub_switch END
							WHERE wsro.update_flag = 0");

			// empty temp table
			$wpdb->query("DELETE FROM {$wpdb->prefix}wsr_meta_all");
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders SET meta_values = ''");

			$s_ids = $wpdb->get_col("SELECT DISTINCT sub_id FROM {$wpdb->prefix}wsr_orders WHERE update_flag = 0");
		
			$v = '';

			foreach ( $s_ids as $id ) {
				$v .= "( ".$id.", '_billing_interval'),( ".$id.", '_billing_period'),
						( ".$id.", '_schedule_end'),( ".$id.", '_schedule_trial_end'), ";
			}

			$wpdb->query("INSERT INTO {$wpdb->prefix}wsr_meta_all(post_id, meta_key) VALUES ". substr($v, 0, (strlen($v)-2)));

			// query for getting the order related postmeta
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
						SET wsro.meta_values = (SELECT GROUP_CONCAT( IFNULL(pm.meta_value,'-') ORDER BY temp.meta_key SEPARATOR ' #wsr# ') AS meta_values
											FROM {$wpdb->prefix}wsr_meta_all as temp 
												LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.meta_key = temp.meta_key AND pm.post_id = temp.post_id)
											WHERE temp.post_id = wsro.sub_id)
						WHERE wsro.update_flag = 0");

			//Code for transposing the order related postmeta concated data
			// billing_interval_months is obtained by converting the interval to months
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
							JOIN (SELECT wsro1.sub_id AS sid, 
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 1), ' #wsr# ', -1) AS billing_interval,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 2), ' #wsr# ', -1) AS billing_period,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 3), ' #wsr# ', -1) AS schedule_end,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 4), ' #wsr# ', -1) AS schedule_trial_end
									FROM {$wpdb->prefix}wsr_orders AS wsro1
									WHERE wsro1.meta_values != '') AS temp ON (temp.sid = wsro.sub_id)
							SET wsro.sub_billing_interval = temp.billing_interval,
								wsro.sub_billing_period = temp.billing_period,
								wsro.sub_billing_interval_months = (CASE temp.billing_period
																		WHEN 'day' THEN (CASE 
																							WHEN temp.billing_interval > 30 THEN ((1 / temp.billing_interval) * 30)
																                            ELSE 1
																                        END)
																		WHEN 'week' THEN ( CASE 
																								WHEN temp.billing_interval > 4 THEN ((1 / (temp.billing_interval * 7)) * 30)
                             																	ELSE 1
                         																	END)
																		WHEN 'month' THEN ((1 / (temp.billing_interval * 30)) * 30)
																		WHEN 'year' THEN ((1 / (temp.billing_interval * 365)) * 30)
																	END),
								wsro.sub_end = temp.schedule_end,
								wsro.sub_trial_end = temp.schedule_trial_end
							WHERE wsro.update_flag = 0
								AND wsro.sub_id = temp.sid");

			// empty temp table
			$wpdb->query("DELETE FROM {$wpdb->prefix}wsr_meta_all");
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders SET meta_values = ''");


			//query to get the count of line items
			$query = "SELECT COUNT(oi.order_item_type) AS count,
							oi.order_id AS order_id
						FROM {$wpdb->prefix}woocommerce_order_items AS oi
							JOIN {$wpdb->prefix}wsr_orders AS wsro
							ON (wsro.order_id = oi.order_id
								AND oi.order_item_type = 'line_item'
								AND wsro.update_flag = 0)
						GROUP BY order_id";
			$results = $wpdb->get_results($query, 'ARRAY_A');

			$o_li_single = array(); // array of order ids having single line item

			if ( count($results) > 0 ) {
				foreach ( $results as $row ) {

					if($row['count'] > 1) {
						continue;
					}

					$o_li_single[] = $row['order_id'];
				}
			}

			// query for getting the product id
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
						SET wsro.p_id = (SELECT oim.meta_value
											FROM {$wpdb->prefix}woocommerce_order_items AS oi
												JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim 
													ON (oim.order_item_id = oi.order_item_id
														AND oim.meta_key = '_product_id'
														AND oi.order_item_type = 'line_item')
											WHERE oi.order_id = wsro.sub_id LIMIT 0,1)
						WHERE wsro.update_flag = 0");

			// query for getting the order item id
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
						SET wsro.o_item_id = (SELECT oim.order_item_id
											FROM {$wpdb->prefix}woocommerce_order_items AS oi
												JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim 
													ON (oim.order_item_id = oi.order_item_id 
														AND oim.meta_key = '_product_id'
														AND oi.order_item_type = 'line_item')
											WHERE oim.meta_value = wsro.p_id 
												AND oi.order_id = wsro.order_id
											LIMIT 0,1)
						WHERE wsro.update_flag = 0");

			$oi_ids = $wpdb->get_col("SELECT DISTINCT o_item_id FROM {$wpdb->prefix}wsr_orders WHERE update_flag = 0");

			$v = '';

			foreach ( $oi_ids as $id ) {
				$v .= "( ".$id.", '_line_total'),( ".$id.", '_qty'),( ".$id.", '_variation_id'),( ".$id.", '_wcs_migrated_recurring_line_total'), ";
			}

			$wpdb->query("INSERT INTO {$wpdb->prefix}wsr_meta_all(post_id, meta_key) VALUES ". substr($v, 0, (strlen($v)-2)));

			// query for getting the order item related postmeta
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
						SET wsro.meta_values = (SELECT GROUP_CONCAT( IFNULL(oim.meta_value,'-') ORDER BY temp.meta_key SEPARATOR ' #wsr# ') AS meta_values
											FROM {$wpdb->prefix}wsr_meta_all as temp 
												LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim 
													ON (oim.order_item_id = temp.post_id AND oim.meta_key = temp.meta_key)
											WHERE temp.post_id = wsro.o_item_id)
						WHERE wsro.update_flag = 0");

			//Code for transposing the order item related postmeta concated data
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
							JOIN (SELECT wsro1.order_id AS oid, 
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 1), ' #wsr# ', -1) AS tot,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 2), ' #wsr# ', -1) AS qty,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 3), ' #wsr# ', -1) AS v_id,
										SUBSTRING_INDEX(SUBSTRING_INDEX(wsro1.meta_values, ' #wsr# ', 4), ' #wsr# ', -1) AS migrated_tot
									FROM {$wpdb->prefix}wsr_orders AS wsro1
									WHERE wsro1.meta_values != '') AS temp ON (temp.oid = wsro.order_id)
							SET wsro.total = CASE WHEN wsro.sub_switch > 0 THEN temp.migrated_tot ELSE temp.tot END,
								wsro.qty = temp.qty,
								wsro.v_id = temp.v_id
							WHERE wsro.update_flag = 0");

			// ###############################
			// Queries for handling manual refunds
			// ###############################
			
			// query for getting the shop_order_refund data
			$wpdb->query("INSERT INTO {$wpdb->prefix}wsr_orders (sub_id, order_id, p_id, v_id, order_date, 
																order_time, type, order_status, sub_status, 
																user_id, cust_email, cust_name, qty, total,
																currency, payment_method, sub_billing_period, 
																sub_billing_interval, sub_billing_interval_months,
																sub_trial_end, sub_end, is_renewal)
							SELECT wsro.sub_id, p.id, wsro.p_id, wsro.v_id, DATE(p.post_date), TIME(p.post_date),
								p.post_type, p.post_status, wsro.sub_status, wsro.user_id, wsro.cust_email, wsro.cust_name, 
								wsro.qty, 0, wsro.currency, wsro.payment_method, wsro.sub_billing_period, wsro.sub_billing_interval, 
								wsro.sub_billing_interval_months, wsro.sub_trial_end, wsro.sub_end, wsro.is_renewal
							FROM {$wpdb->prefix}posts as p
								JOIN {$wpdb->prefix}wsr_orders as wsro ON (wsro.order_id = p.post_parent
																		AND wsro.update_flag = 0
																		AND p.post_type = 'shop_order_refund')");

			// query for getting the order item id
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
						SET wsro.o_item_id = (SELECT oim.order_item_id
											FROM {$wpdb->prefix}woocommerce_order_items AS oi
												JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim 
													ON (oim.order_item_id = oi.order_item_id 
														AND oim.meta_key IN ('_product_id', '_variation_id')
														AND oi.order_item_type = 'line_item')
											WHERE (oim.meta_value = wsro.p_id OR oim.meta_value = wsro.v_id) 
												AND oi.order_id = wsro.order_id
											LIMIT 0,1)
						WHERE wsro.update_flag = 0
							AND wsro.type = 'shop_order_refund'");

			//Code for updating the refund total
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
							JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim 
								ON (oim.order_item_id = wsro.o_item_id
									AND oim.meta_key = '_line_total')
							SET wsro.total = oim.meta_value
							WHERE wsro.type = 'shop_order_refund'
									AND wsro.o_item_id > 0
									AND wsro.update_flag = 0");

			//Code for updating the refund total [if not updated][partial refund done at order level]
			if( !empty($o_li_single) ) {
				$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
	                             JOIN {$wpdb->prefix}posts AS p
	                             	ON (p.id = wsro.order_id
	                             		AND p.post_parent IN (". implode(',', $o_li_single) ."))
	                             JOIN {$wpdb->prefix}postmeta AS pm
	                                 ON (pm.post_id = p.id
	                                     AND pm.meta_key = '_order_total')
	                             SET wsro.total = pm.meta_value
	                             WHERE wsro.type = 'shop_order_refund'
	                                     AND wsro.update_flag = 0
	                                     AND wsro.total = 0");

			}
			
			//Code for updating the refund qty
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
							JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim 
								ON (oim.order_item_id = wsro.o_item_id
									AND oim.meta_key = '_qty')
							SET wsro.qty = oim.meta_value
							WHERE wsro.type = 'shop_order_refund'
									AND wsro.update_flag = 0");

			//query to update the price for simple prods
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
							JOIN {$wpdb->prefix}postmeta AS pm 
								ON (pm.post_id = wsro.p_id
									AND pm.meta_key = '_price')
							SET wsro.price = pm.meta_value
							WHERE wsro.v_id = 0
									AND wsro.update_flag = 0");

			//query to update the price for variable prods
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
							JOIN {$wpdb->prefix}postmeta AS pm 
								ON (pm.post_id = wsro.v_id
									AND pm.meta_key = '_price')
							SET wsro.price = pm.meta_value
							WHERE wsro.v_id > 0
									AND wsro.update_flag = 0");

			//query to update the switch subscription ids
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders AS wsro
							SET wsro.sub_switch = (SELECT IFNULL(MAX(temp.order_id) ,0)
													FROM (SELECT order_id, sub_id 
															FROM {$wpdb->prefix}wsr_orders) AS temp 
															WHERE temp.sub_id = wsro.sub_id 
																AND temp.order_id < wsro.order_id )
							WHERE wsro.sub_switch > 0
								AND wsro.update_flag = 0");

			//query to delete the order level refund entries
			$wpdb->query("DELETE FROM {$wpdb->prefix}wsr_orders
							WHERE total = 0
								AND qty = 0
								AND  type = 'shop_order_refund'
								AND update_flag = 0");

			// empty temp table
			$wpdb->query("DELETE FROM {$wpdb->prefix}wsr_meta_all");
			$wpdb->query("UPDATE {$wpdb->prefix}wsr_orders 
							SET meta_values = '', 
								o_item_id = 0,
								update_flag = 1
							WHERE update_flag = 0");


			if ( !empty($_POST['sfinal']) && $_POST['sfinal'] == 1 ) {

				//query to delete the additional refund records
				$wpdb->query("DELETE FROM {$wpdb->prefix}wsr_orders 
								WHERE sub_id IN (SELECT sub_id FROM (SELECT sub_id FROM {$wpdb->prefix}wsr_orders
													WHERE type = 'shop_order_refund'
													GROUP BY order_id HAVING COUNT(order_id) > 1 ) AS temp)
									AND type = 'shop_order_refund'");

				$wpdb->query("DROP TABLE {$wpdb->prefix}wsr_meta_all");
				$wpdb->query("ALTER TABLE {$wpdb->prefix}wsr_orders DROP COLUMN meta_values, DROP COLUMN o_item_id, DROP COLUMN update_flag,
							ADD PRIMARY KEY(sub_id,order_id,p_id,v_id)");
			}

			exit;

		}
	}

}