<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       www.idomit.com
 * @since      1.0.0
 *
 * @package    Single_Product_Multivendor
 * @subpackage Single_Product_Multivendor/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Single_Product_Multivendor
 * @subpackage Single_Product_Multivendor/public
 * @author     idomit <info@idomit.com>
 */
class Single_Product_Multivendor_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Single_Product_Multivendor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Single_Product_Multivendor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/single-product-multivendor-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Single_Product_Multivendor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Single_Product_Multivendor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/single-product-multivendor-public.js', array( 'jquery' ), $this->version, false );

	}
	public function wcfm_add_product_to_store ( $menus, $user) {
		global $WCFM, $user;
			if ( isset( $user->roles ) && is_array( $user->roles ) )
			if ( in_array( 'administrator', $user->roles ) )
			$add_menu_upload_website_files = array(                          
				'wcfm-upload-website-files' => array(   'label'  => __( 'Add Product To Store', 'wcfm-custom-menus'),
					'url'       => get_wcfm_custom_menus_url( 'wcfm-add-product-store' ),
					'icon'      => 'fa-upload',
					'priority'  => 5.2
					)
					);
				$menus = array_merge( $menus, $add_menu_upload_website_files );
	
			return $menus;
	}
	public function wdm_add_custom_order_line_item_meta( $item_id, $values )
	{
		// echo '<pre>';
		// print_r($values);
		if( isset($values[0]['name']) && $values[0]['name'] == 'access_store' ){
			
			// $item->update_meta_data( 'access_store', $values[0]['value'] );
			wc_add_order_item_meta($item_id,'access_store',$values[0]['value']);
		}
	    // exit;
	}
	public function spmv_checkout_order_processed( $order_id, $order_posted, $order = '' ) {
		global $WCFM, $WCFMmp, $wpdb;
		
		if( !$order_id ) return;
		
		// if ( get_post_meta( $order_id, '_wcfmmp_order_processed', true ) ) return;
		
		if (!$order)
      $order = wc_get_order( $order_id );
      
    if( !is_a( $order , 'WC_Order' ) ) return;
    
    $wcfmmp_order_processed = false;
    
    $customer_id = 0;
    if ( $order->get_user_id() ) 
    	$customer_id = $order->get_user_id();
    
    $payment_method = ! empty( $order->get_payment_method() ) ? $order->get_payment_method() : '';
    $order_status = $commission_status = $order->get_status();
    $shipping_status = 'pending'; 
    $is_withdrawable = 1;
    $is_auto_withdrawal = 0;
    
    $disallow_payment_methods = get_wcfm_marketplace_disallow_active_order_payment_methods();
    $withdrawal_reverse = isset( $WCFMmp->wcfmmp_withdrawal_options['withdrawal_reverse'] ) ? 'yes' : '';
    if( $withdrawal_reverse && !empty( $disallow_payment_methods ) && in_array( $payment_method, array_keys( $disallow_payment_methods ) ) ) {
    	$is_auto_withdrawal = 1;
    }
    
    // Set Shipping Status Complete for Virtual Products
    if( !$order->get_formatted_shipping_address() ) {
    	$shipping_status = 'completed'; 
    }
   
    // Ger Shipping Vendor Packages
    $vendor_shipping = array();
    if( $WCFMmp && $WCFMmp->wcfmmp_shipping ) {
    	$vendor_shipping = $WCFMmp->wcfmmp_shipping->get_order_vendor_shipping( $order );
    }
   
	$items = $order->get_items( 'line_item' );
	
    if( !empty( $items ) ) {
			$WCFMmp_Commission = new WCFMmp_Commission(); 
			foreach( $items as $item_id => $item ) {
				$order_item_id = $item->get_id();
				$access_store = wc_get_order_item_meta( $order_item_id, 'access_store',true );
				// Check whether order item already processed or not
				// $order_item_processed = wc_get_order_item_meta( $order_item_id, '_wcfmmp_order_item_processed', true );
				// if( $order_item_processed ) continue;
				
				$line_item = new WC_Order_Item_Product( $item );
				$product  = $line_item->get_product();
				$product_id = $line_item->get_product_id();
				$variation_id = $line_item->get_variation_id();
				
				if( $product_id ) {
					$vendor_id = false;
					if(!empty($access_store)){
						$vendor_id = $access_store;
					}
					// $vendor_id = wcfm_get_vendor_id_by_post( $product_id );
					
					if( $vendor_id ) {
						
						// Updating Order Item meta with Vendor ID
						wc_update_order_item_meta( $order_item_id, '_vendor_id', $vendor_id );
						
						$discount_amount   = 0;
						$discount_type     = '';
						$other_amount      = 0;
						$other_amount_type = '';
						$withdraw_charges  = 0;
						$refunded_qty      = 0;
						$refund_status     = 'pending';
						$refunded_amount   = $refunded_total_tax = $refunded_shipping_amount = $refunded_shipping_tax = 0;
						$grosse_total      = $gross_tax_cost = $gross_shipping_cost = $gross_shipping_tax = $gross_sales_total = 0;
						$total_commission  = $commission_tax = $commission_amount = $tax_cost = $shipping_cost = $shipping_tax = $transaction_charge = 0;
						$is_partially_refunded = 0;
						
						// Item Refunded Amount
						if ( $refunded_amount = $order->get_total_refunded_for_item( $order_item_id ) ) {
							$refunded_qty = $order->get_qty_refunded_for_item( $order_item_id );
							$is_partially_refunded = 1;
							$refund_status = 'completed';
						}
						
						// Item commission calculation
						$commission_rule = '';
						if( $WCFMmp->wcfmmp_vendor->is_vendor_deduct_discount( $vendor_id, $order_id ) ) {
							$commission_rule   = $WCFMmp->wcfmmp_product->wcfmmp_get_product_commission_rule( $product_id, $variation_id, $vendor_id, ( $line_item->get_total() - $refunded_amount ), ( $line_item->get_quantity() - $refunded_qty ), $order_id );
							$commission_amount = $WCFMmp_Commission->wcfmmp_get_order_item_commission( $order_id, $vendor_id, $product_id, $variation_id, ( $line_item->get_total() - $refunded_amount ), ( $line_item->get_quantity() - $refunded_qty ), $commission_rule );
							$grosse_total      = $line_item->get_total();
							$commission_rule['coupon_deduct'] = 'yes';
						} else {
							$commission_rule   = $WCFMmp->wcfmmp_product->wcfmmp_get_product_commission_rule( $product_id, $variation_id, $vendor_id, ( $line_item->get_subtotal() - $refunded_amount ), ( $line_item->get_quantity() - $refunded_qty ), $order_id );
							$commission_amount = $WCFMmp_Commission->wcfmmp_get_order_item_commission( $order_id, $vendor_id, $product_id, $variation_id, ( $line_item->get_subtotal() - $refunded_amount ), ( $line_item->get_quantity() - $refunded_qty ), $commission_rule );
							$grosse_total      = $line_item->get_subtotal();
						}
						$gross_sales_total   = $grosse_total;
						$total_commission    = $commission_amount;
						
						$discount_amount     = ( $line_item->get_subtotal() - $line_item->get_total() );
						
						// Shipping commission calculation
						if ( !empty($vendor_shipping) && isset($vendor_shipping[$vendor_id]) && $product->needs_shipping() ) {
							$shipping_cost            = (float) round(($vendor_shipping[$vendor_id]['shipping'] / $vendor_shipping[$vendor_id]['package_qty']) * $line_item->get_quantity(), 2);
							$shipping_tax             = (float) round(($vendor_shipping[$vendor_id]['shipping_tax'] / $vendor_shipping[$vendor_id]['package_qty']) * $line_item->get_quantity(), 2);
							$refunded_shipping_amount = (float) round(($vendor_shipping[$vendor_id]['refunded_amount'] / $vendor_shipping[$vendor_id]['package_qty']) * $line_item->get_quantity(), 2);
							$refunded_shipping_tax    = (float) round(($vendor_shipping[$vendor_id]['refunded_tax'] / $vendor_shipping[$vendor_id]['package_qty']) * $line_item->get_quantity(), 2);
						}
						$gross_shipping_cost = $shipping_cost;
						$gross_shipping_tax  = $shipping_tax;
						$shipping_cost       = apply_filters( 'wcfmmmp_commission_shipping_cost', ( $shipping_cost - $refunded_shipping_amount ), $vendor_shipping, $order_id, $vendor_id, $product_id, $commission_rule );
						$shipping_tax        = apply_filters( 'wcfmmmp_commission_shipping_tax', ( $shipping_tax - $refunded_shipping_tax ), $vendor_shipping, $order_id, $vendor_id, $product_id, $commission_rule );
						
						// Commission Rule on Shipping Cost - by default false
						if( apply_filters( 'wcfmmp_is_allow_commission_on_shipping', false ) ) {
							$shipping_cost = $WCFMmp_Commission->wcfmmp_generate_commission_cost( $shipping_cost, apply_filters( 'wcfmmmp_shipping_commission_rule', $commission_rule ) );
							$shipping_tax  = $WCFMmp_Commission->wcfmmp_generate_commission_cost( $shipping_tax, apply_filters( 'wcfmmmp_shipping_commission_rule', $commission_rule ) );
						}
						
						$commission_rules['shipping_for'] = 'admin';
						if( $get_shipping = $WCFMmp->wcfmmp_vendor->is_vendor_get_shipping( $vendor_id ) ) {
							$grosse_total 		+= (float) $gross_shipping_cost;
							$total_commission += (float) $shipping_cost;
							$commission_rules['shipping_for'] = 'vendor';
						}
						$gross_sales_total  += (float) $gross_shipping_cost;
						
						// Tax commission calculation
						$gross_tax_cost = $line_item->get_total_tax();
						if ( wc_tax_enabled() ) {
							$order_taxes         = $order->get_taxes();
							$tax_data = $item->get_taxes();
							if ( ! empty( $tax_data ) ) {
								foreach ( $order_taxes as $tax_item ) {
									$tax_item_id         = $tax_item['rate_id'];
									$refunded_total_tax += $order->get_tax_refunded_for_item( $order_item_id, $tax_item_id );
								}
							}
						}
						$tax_cost       = apply_filters( 'wcfmmmp_commission_tax_cost', ( $line_item->get_total_tax() - $refunded_total_tax ), $commission_amount, $order_id, $vendor_id, $product_id, $commission_rule );
						
						// Commission Rule on Tax Cost - by default false
						if( apply_filters( 'wcfmmp_is_allow_commission_on_tax', false ) ) {
							$tax_cost = $WCFMmp_Commission->wcfmmp_generate_commission_cost( $tax_cost, apply_filters( 'wcfmmmp_tax_commission_rule', $commission_rule ) );
						}
						
						$commission_rules['tax_for'] = 'admin';
						if( $WCFMmp->wcfmmp_vendor->is_vendor_get_tax( $vendor_id ) ) {
							$grosse_total 		+= (float) $gross_tax_cost;
							$total_commission += (float) $tax_cost;
							if( $get_shipping ) {
								$grosse_total 		+= (float) $gross_shipping_tax;
								$total_commission += (float) $shipping_tax;
							}
							$commission_rules['tax_for'] = 'vendor';
						}
						$gross_sales_total  += (float) $gross_tax_cost;
						$gross_sales_total  += (float) $gross_shipping_tax;
						
						// Purchase Price
						$purchase_price = get_post_meta( $product_id, '_purchase_price', true );
						if( !$purchase_price ) $purchase_price = $product->get_price();
						
						$is_auto_withdrawal = apply_filters( 'wcfmmp_is_auto_withdrawal', $is_auto_withdrawal, $vendor_id, $order_id, $order, $payment_method );
						
						// Transaction Charge Calculation
						if( isset( $commission_rule['transaction_charge_type'] ) && ( $commission_rule['transaction_charge_type'] != 'no' ) ) {
							$vendor_order_amount = $WCFMmp_Commission->wcfmmp_calculate_vendor_order_commission( $vendor_id, $order_id, $order, false );
							$vendor_order_total_commission = (float)$vendor_order_amount['commission_amount'];
							$vendor_order_total_item       = absint( $vendor_order_amount['item_count'] );
							$total_transaction_charge = 0;
							if( ( $commission_rule['transaction_charge_type'] == 'percent' ) || ( $commission_rule['transaction_charge_type'] == 'percent_fixed' ) ) {
								$total_transaction_charge  += $vendor_order_total_commission * ( (float)$commission_rule['transaction_charge_percent'] / 100 );
							}
							if( ( $commission_rule['transaction_charge_type'] == 'fixed' ) || ( $commission_rule['transaction_charge_type'] == 'percent_fixed' ) ) {
								$total_transaction_charge  += (float)$commission_rule['transaction_charge_fixed'];
							}
							$total_transaction_charge = round( $total_transaction_charge, 2 );
							$transaction_charge       = (float) $total_transaction_charge / $vendor_order_total_item;
							$transaction_charge       = apply_filters( 'wcfmmp_commission_deducted_transaction_charge', $transaction_charge, $vendor_id, $product_id, $order_id, $total_commission, $commission_rule );
							
							// $transaction_charge round check
							if( !get_post_meta( $order_id, '_wcfmmp_vendor_transacton_charge_adjusted_'.$vendor_id, true ) ) {
								$re_total_transaction_charge = round($transaction_charge, 2) * $vendor_order_total_item;
								if( $re_total_transaction_charge != $total_transaction_charge ) {
									$transaction_charge += ( $total_transaction_charge - $re_total_transaction_charge );
								}
								update_post_meta( $order_id, '_wcfmmp_vendor_transacton_charge_adjusted_'.$vendor_id, 'yes' );
							}
							
							$total_commission      -= (float) $transaction_charge;
						}
						
						// Commission Tax Calculation
						if( isset( $commission_rule['tax_enable'] ) && ( $commission_rule['tax_enable'] == 'yes' ) ) {
							$commission_tax = $total_commission * ( (float)$commission_rule['tax_percent'] / 100 );
							$commission_tax = apply_filters( 'wcfmmp_commission_deducted_tax', $commission_tax, $vendor_id, $product_id, $order_id, $total_commission, $commission_rule );
							$total_commission -= (float) $commission_tax;
						}
						
						// Withdrawal Charges Calculation
						if( !$is_auto_withdrawal ) {
							$withdraw_charges = $WCFMmp->wcfmmp_withdraw->calculate_withdrawal_charges( $total_commission, $vendor_id );
						}
						
						$wpdb->query(
										$wpdb->prepare(
											"INSERT INTO `{$wpdb->prefix}wcfm_marketplace_orders` 
													( vendor_id
													, order_id
													, customer_id
													, payment_method
													, product_id
													, variation_id
													, quantity
													, product_price
													, purchase_price
													, item_id
													, item_type
													, item_sub_total
													, item_total
													, shipping
													, tax
													, shipping_tax_amount
													, commission_amount
													, discount_amount
													, discount_type
													, other_amount
													, other_amount_type
													, refunded_amount
													, withdraw_charges
													, total_commission
													, order_status
													, commission_status
													, shipping_status 
													, is_withdrawable
													, is_auto_withdrawal
													, is_partially_refunded
													, refund_status
													, created
													) VALUES ( %d
													, %d
													, %d
													, %s
													, %d
													, %d 
													, %d
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %s
													, %d
													, %d
													, %d
													, %s
													, %s
													) ON DUPLICATE KEY UPDATE `item_id` = %d"
											, $vendor_id
											, $order_id
											, $customer_id
											, $payment_method
											, $product_id
											, $variation_id
											, $line_item->get_quantity()
											, $product->get_price()
											, $purchase_price
											, $order_item_id
											, $line_item->get_type()
											, $line_item->get_subtotal()
											, $line_item->get_total()
											, $shipping_cost
											, $tax_cost
											, $shipping_tax
											, round($commission_amount, 2)
											, round($discount_amount, 2)
											, $discount_type
											, round($other_amount, 2)
											, $other_amount_type
											, ( $refunded_amount + $refunded_total_tax + $refunded_shipping_amount + $refunded_shipping_tax )
											, $withdraw_charges
											, round($total_commission, 2)
											, $order_status
											, $commission_status
											, $shipping_status 
											, $is_withdrawable
											, $is_auto_withdrawal
											, $is_partially_refunded
											, $refund_status
											, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )
											, $order_item_id
							)
						);
						$commission_id = $wpdb->insert_id;
						
						// Update Commission Metas
						$WCFMmp_Commission->wcfmmp_update_commission_meta( $commission_id, 'currency', $order->get_currency() );
						$WCFMmp_Commission->wcfmmp_update_commission_meta( $commission_id, 'gross_total', round($grosse_total, 2) );
						$WCFMmp_Commission->wcfmmp_update_commission_meta( $commission_id, 'gross_sales_total', round($gross_sales_total, 2) );
						$WCFMmp_Commission->wcfmmp_update_commission_meta( $commission_id, 'gross_shipping_cost', round($gross_shipping_cost, 2) );
						$WCFMmp_Commission->wcfmmp_update_commission_meta( $commission_id, 'gross_shipping_tax', round($gross_shipping_tax, 2) );
						$WCFMmp_Commission->wcfmmp_update_commission_meta( $commission_id, 'gross_tax_cost', round($gross_tax_cost, 2) );
						$WCFMmp_Commission->wcfmmp_update_commission_meta( $commission_id, 'commission_tax', round($commission_tax, 2) );
						$WCFMmp_Commission->wcfmmp_update_commission_meta( $commission_id, 'transaction_charge', round($transaction_charge, 2) );
						$WCFMmp_Commission->wcfmmp_update_commission_meta( $commission_id, 'commission_rule', serialize( $commission_rule ) );
						
						do_action( 'wcfmmp_order_item_processed', $commission_id, $order_id, $order, $vendor_id, $product_id, $order_item_id, $grosse_total, $total_commission, $is_auto_withdrawal, $commission_rule );
						
						// Updating Order Item meta processed
						wc_update_order_item_meta( $order_item_id, '_wcfmmp_order_item_processed', $commission_id );
						wc_delete_order_item_meta( $order_item_id, 'access_store' );    
					}
					
					$wcfmmp_order_processed = true;
				}
				
				// Affiliate Unset from Session
				if( apply_filters( 'wcfmmp_is_allow_reset_affiliate_after_order_process', false ) && WC()->session && WC()->session->get( 'wcfm_affiliate' ) ) {
					WC()->session->__unset( 'wcfm_affiliate' );
				}
			}
		}
		if( $wcfmmp_order_processed ) {
			update_post_meta( $order_id, '_wcfmmp_order_processed', 'yes' );
			
			$wcfmmp_order_email_triggered = get_post_meta( $order_id, '_wcfmmp_order_email_triggered', true );
			if( !$wcfmmp_order_email_triggered ) {
				$store_new_order_email_allowed_order_status = get_wcfm_store_new_order_email_allowed_order_status();
				$current_order_status = 'wc-'.$order_status;
				if( isset( $store_new_order_email_allowed_order_status[$current_order_status] ) ) {
					$wcfmmp_email = WC()->mailer()->emails['WCFMmp_Email_Store_new_order'];
					if( $wcfmmp_email ) {
						$wcfmmp_email->trigger( $order_id );
						update_post_meta( $order_id, '_wcfmmp_order_email_triggered', 'yes' );
					}
				}
			}
			
			do_action( 'wcfmmp_order_processed', $order_id, $is_auto_withdrawal );
		}
       
		return;
	}
	
	public function store_taxonomy_display($vendor_taxonomies, $store_id , $taxonomy  ) {
		global $WCFMmp, $wpdb, $WCFM;
		$WCFMmp_Store = new WCFMmp_Store();
		
		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'product',
			'post_status'      => array('publish'),
			'suppress_filters' => 0 
		);
		$args['meta_query'] = array(
			'relation' => 'OR', // both of below conditions must match
						array(
							'key' => '_spmv_post_author',
						'value' => $store_id,
						'compare' => '='
						),
				array(
						
					'relation' => 'AND', 
		array (
		'key' => '_spmv_exclude_stores',
		'value' => '.*;s:[0-9]+:"'.$store_id.'";.*',
		'compare' => 'NOT REGEXP',
		),
		array(
							'key' => '_spmv_all_stores',
							'value' => 'true',
							'compare' => '='
						),
					//),
					
		),
			);
		$vendor_products = get_posts( $args );
		// $vendor_taxonomies = wp_get_post_terms(get_the_ID(),'product_cat',array('fields'=>'ids'));
		// print_r($vendor_taxonomies);
		// $vendor_tax_migrated = get_user_meta( $WCFMmp_Store->get_id(), '_wcfm_vendor_tax_migrated', true );
		
		// if( !$vendor_tax_migrated || apply_filters( 'wcfmmp_force_store_taxonomy_refresh', false ) ) {
			$WCFMmp->wcfmmp_vendor->wcfmmp_reset_vendor_taxonomy($store_id);
			// $vendor_products = $WCFM->wcfm_vendor_support->wcfm_get_products_by_vendor( $WCFMmp_Store->get_id(), 'publish' ); 
			if( !empty( $vendor_products ) ) {
				foreach( $vendor_products  as $vendor_product_id => $vendor_product ) {
					$pcategories = get_the_terms( $vendor_product, $taxonomy );
					if( !empty($pcategories) ) {
						foreach($pcategories as $pkey => $pcategory) {
							$WCFMmp->wcfmmp_vendor->wcfmmp_save_vendor_taxonomy( $store_id, $vendor_product_id, $pcategory->term_id );
						}
					}
				}
			}
		// 	delete_user_meta( $WCFMmp_Store->get_id(), '_wcfm_store_product_cats' );
		// 	update_user_meta( $WCFMmp_Store->get_id(), '_wcfm_vendor_tax_migrated', 'yes' );
		// }
		
		$vendor_taxonomies = $WCFMmp->wcfmmp_vendor->wcfmmp_get_vendor_taxonomy( $store_id, $taxonomy );
	
		return $vendor_taxonomies;
	}
}
