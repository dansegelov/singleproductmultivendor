<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.idomit.com
 * @since      1.0.0
 *
 * @package    Single_Product_Multivendor
 * @subpackage Single_Product_Multivendor/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Single_Product_Multivendor
 * @subpackage Single_Product_Multivendor/admin
 * @author     idomit <info@idomit.com>
 */
class Single_Product_Multivendor_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/single-product-multivendor-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/single-product-multivendor-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * WCFM - Custom Menus Query Var
	 */
	public function wcfmcsm_query_vars( $query_vars ) {
		$wcfm_modified_endpoints = (array) get_option( 'wcfm_endpoints' );
		
		$query_custom_menus_vars = array(
			'wcfm_add_product_to_store'  => ! empty( $wcfm_modified_endpoints['wcfm-add-product-to-store'] ) ? $wcfm_modified_endpoints['wcfm-add-product-to-store'] : 'add-product-to-store',
		);
		
		$query_vars = array_merge( $query_vars, $query_custom_menus_vars );
		
		return $query_vars;
	}
	
	/**
	 * WCFM - Custom Menus End Point Title
	 */
	public function wcfmcsm_endpoint_title( $title, $endpoint ) {
		global $wp;
		switch ( $endpoint ) {
			case 'wcfm_add_product_to_store' :
				$title = __( 'Add Product To Store', 'wcfm-custom-menus' );
			break;
		}
		
		return $title;
	}
	/**
	 * WCFM - Custom Menus Endpoint Intialize
	 */
	public function wcfmcsm_init() {
		global $WCFM_Query;

		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_cms' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_cms', 1 );
		}
	}
	
	/**
	 * WCFM - Custom Menus Endpoiint Edit
	 */
	public function wcfm_custom_menus_endpoints_slug( $endpoints ) {
		
		$custom_menus_endpoints = array(
										'wcfm_add_product_to_store' => 'add-product-to-store',
									);
		
		$endpoints = array_merge( $endpoints, $custom_menus_endpoints );
		
		return $endpoints;
	}
	
	
	public function get_wcfm_custom_menus_url( $endpoint ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_custom_menus_url = wcfm_get_endpoint_url( $endpoint, '', $wcfm_page );
		return $wcfm_custom_menus_url;
	}
	
	/**
	 * WCFM - Custom Menus
	 */
	public function wcfmcsm_wcfm_menus( $menus ) {
		global $WCFM;

		if(!in_array('administrator',  wp_get_current_user()->roles)) {
			return $menus;
		}
		
		$custom_menus = array( 'wcfm_add_product_to_store' => array(   'label'  => __( 'Add Product To Store', 'wcfm-custom-menus'),
													'url'       => $this->get_wcfm_custom_menus_url( 'add-product-to-store' ),
													'icon'      => 'cubes',
													'priority'  => 5.1
													),
									);
		
		$menus = array_merge( $menus, $custom_menus );
			
		return $menus;
	}
	
	/**
	 *  WCFM - Custom Menus Views
	 */
	public function wcfm_csm_load_views( $end_point ) {
		global $WCFM, $WCFMu;
		$plugin_path = trailingslashit( dirname( __FILE__  ) );

		if(!in_array('administrator',  wp_get_current_user()->roles)) {
			return false;
		}
		
		switch( $end_point ) {
			case 'wcfm_add_product_to_store':
				$WCFM->library->load_select2_lib();
        $WCFM->library->load_datatable_lib();
        //wp_enqueue_script( 'wcfm_products_js', $WCFM->library->js_lib_url . 'products/wcfm-script-products.js', array('jquery', 'dataTables_js'), $WCFM->version, true );
        wp_enqueue_script( 'single_products_js', plugin_dir_url( __FILE__ ) . 'js/single-product-script.js', array( 'jquery' ), $this->version, false );

        wp_localize_script( 'single_products_js', 'spmv_params', array('ajax_url'=>WC()->ajax_url()));
				// echo $plugin_path . 'views/wcfm-views-add-product-to-store.php';
				require_once( $plugin_path . 'views/wcfm-views-add-product-to-store.php' );
				//require_once( ABSPATH . 'wp-content/plugins/wc-frontend-manager/views/products/wcfm-view-products.php' );
			break;
		}
	}

	public function save_author_meta_as_metafield( $post_id, $post, $update ) {
	    $post_author_id = get_post_field( 'post_author', $post_id );
	    
	    if($post_author_id && $post_author_id != '')
	    {
	    	update_post_meta( $post_id, '_spmv_post_author', $post_author_id );
	    }
	    // do something with this product
	}

	public function wcfmcsm_wcfm_remove_action_products($actions, $the_product)
	{
		if(in_array('administrator',  wp_get_current_user()->roles)) {
			return $actions;
		}
		$product_id =  $the_product->get_id();
		if($product_id != '')
		{
			$post_author_id = get_post_field( 'post_author', $product_id);

			$current_user_id = get_current_user_id();

			if($post_author_id == '' || $current_user_id == '')
			 		return $actions;
			elseif ($post_author_id == $current_user_id)
				return $actions;
			else
				return ''; 

			 



		}
			
		return $actions;
	}



	
	public function spmv_get_cart_data($cart_item_meta = array(), $cart_item)
	{
		global $WCFM, $WCFMmp;
		
		
		if( !apply_filters( 'wcfmmp_is_allow_cart_sold_by', true ) ) return $cart_item_meta;
		
		if( $WCFMmp->wcfmmp_vendor->is_vendor_sold_by() ) {
			$product_id = $cart_item['product_id'];
			if( !$product_id ) {
				$variation_id 	= sanitize_text_field( $cart_item['variation_id'] );
				if( $variation_id ) {
					$product_id = wp_get_post_parent_id( $variation_id );
				}
			}
			$vendor_id = false;
			if(isset($cart_item[0]['name']) && $cart_item[0]['name'] == 'access_store')
			{
				$vendor_id = $cart_item[0]['value'];
			}
			/*if($vendor_id == '')
			{
				$vendor_id = wcfm_get_vendor_id_by_post( $product_id );
			}*/
			
			if( $vendor_id ) {
				if( apply_filters( 'wcfmmp_is_allow_sold_by', true, $vendor_id ) && wcfm_vendor_has_capability( $vendor_id, 'sold_by' ) ) {
					// Check is store Online
					$is_store_offline = get_user_meta( $vendor_id, '_wcfm_store_offline', true );
					if ( !$is_store_offline ) {
						$sold_by_text = $WCFMmp->wcfmmp_vendor->sold_by_label( absint($vendor_id) );
						//$sold_by_text = 'Store';
						
						if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) {
							$store_name = wcfm_get_vendor_store( absint($vendor_id) );
						} else {
							$store_name = wcfm_get_vendor_store_name( absint($vendor_id) );
						}
						
						do_action('before_wcfmmp_sold_by_label_cart_page', $vendor_id, $product_id );
						if( !is_array( $cart_item_meta ) ) $cart_item_meta = (array) $cart_item_meta;
						$cart_item_meta = array_merge( $cart_item_meta, array( array( 'name' => $sold_by_text, 'value' => $store_name ) ) );
						do_action('after_wcfmmp_sold_by_label_cart_page', $vendor_id, $product_id );
					}
				}
			}
		}
		return $cart_item_meta;
	}

	public function plugin_republic_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		 //print_r($cart_item_data);
		 $post_author_id = get_post_field( 'post_author', $product_id);
		 if(!empty($post_author_id))
		 {
		 	//echo 'test';
		 	$userdata = get_userdata($post_author_id);
		 	if(in_array('administrator',  $userdata->roles)) {
		 		//echo 'test';
		 		//echo $_SESSION['access_store'];
				if( isset( $_SESSION['access_store'] ) && $_SESSION['access_store'] != '' ) {
			 		//$cart_item_data['access_store'] = $_SESSION['access_store'];
			 		$cart_item_data = array_merge( $cart_item_data, array( array( 'name' => 'access_store', 'value' =>  $_SESSION['access_store'] ) ) );
			 	}
			}
		 }
		 
		 //print_r($cart_item_data);
		 return $cart_item_data;
	}
	public function before_wcfmmp_sold_by_label_cart_page( $vendor_id, $product_id ) {
		 //print_r($cart_item_data);
	/*	$cartdata = WC()->cart->get_cart();
		 echo '<pre>';
		 print_r($cartdata);
		 exit;*/
		 $post_author_id = get_post_field( 'post_author', $product_id);
		 if(!empty($post_author_id))
		 {
		 	$userdata = get_userdata($post_author_id);
		 	if(in_array('administrator',  $userdata->roles)) {
				if( isset( $_SESSION['access_store'] ) && $_SESSION['access_store'] != '' ) {
			 		$cart_item_data['access_store'] = $_SESSION['access_store'];
			 	}
			}
		 }
		 
		 
		 return $cart_item_data;
	}

	public function check_page_and_store_session()
	{
		//session_start();
		if ( wcfmmp_is_store_page() ) {
      	$store_user = wcfmmp_get_store( get_query_var( 'author' ) );

				if ( $store_user->id ) {
					$store_id = $store_user->id;
					$_SESSION['access_store'] = $store_id;
				}
		}
	}
	public function wcfmcsm_wcfm_show_products($args)
	{
		if(isset($_POST['action']) && $_POST['action'] == 'wcfm_ajax_controller')
		{
			if(isset($args['author']) && $args['author'] !='')
			{
				$user_id = $args['author'];
				unset($args['author']);

				$args['meta_query'] = array(
															'relation' => 'OR', // both of below conditions must match
																array(
																	'key' => '_spmv_post_author',
														        'value' => $user_id,
														        'compare' => '='
																),
														array(
																
														      'relation' => 'AND', 
	                                array (
	                                 'key' => '_spmv_exclude_stores',
	                                 'value' => '.*;s:[0-9]+:"'.$user_id.'";.*',
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
			}
			//print_r($args);
			return $args;
		}
		//echo '<pre>';
		//print_r($args);
		//exit;
	}

	public function exclude_store_from_products()
	{
		
		if( isset( $_POST['proid'] ) && !empty( $_POST['proid'] ) ) {
			$storeids=array();
			if(!empty($_POST['exclude']))
			{
				$storeids= $_POST['exclude']; 
			}
			update_post_meta( $_POST['proid'], '_spmv_exclude_stores', $storeids );
			exit;
		}
	}

	public function assign_all_stores_to_products()
	{
		
		if( isset( $_POST['proid'] ) && !empty( $_POST['proid'] ) ) {
			
			$allstores = 'false';
			if($_POST['is_checked'] == 'true')
			{
				//echo 'test';
				$allstores = 'true'; 
			}
			update_post_meta( $_POST['proid'], '_spmv_all_stores', $allstores );
			exit;
		}
	}
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$wcfmu_products_status = apply_filters( 'wcfmu_products_menus', array(  
																																			'publish' => __( 'Published', 'wc-frontend-manager'),
																																			'draft' => __( 'Draft', 'wc-frontend-manager'),
																																			'pending' => __( 'Pending', 'wc-frontend-manager'),
																																			'archived' => __( 'Archived', 'wc-frontend-manager')
																																		) );
		
		$length = sanitize_text_field( $_POST['length'] );
		$offset = sanitize_text_field( $_POST['start'] );
		
		if( class_exists('WooCommerce_simple_auction') ) {
			remove_all_filters( 'pre_get_posts' );
		}
		$administrator_arr = array(
			'role' => 'administrator',
		);
		$administrator = get_users($administrator_arr);
		$users_arr = array();
		foreach ( $administrator as $administratorid ) {
			$users_arr[] = $administratorid->ID;
		}
		// print_r($users_arr);
		$args = array(
							'posts_per_page'   => $length,
							'offset'           => $offset,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => '',
							'meta_value'       => '',
							'post_type'        => 'product',
							'post_mime_type'   => '',
							'post_parent'      => '',
							'author__in'	   => $users_arr,
							'post_status'      => array('draft', 'pending', 'publish', 'private', 'scheduled' ),
							'suppress_filters' => 0 
						);
		$for_count_args = $args;
		
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) {
			$args['s'] = $_POST['search']['value'];
		}
		
		if( isset($_POST['product_status']) && !empty($_POST['product_status']) && ( $_POST['product_status'] != 'any' ) ) $args['post_status'] = sanitize_text_field( $_POST['product_status'] );
  	
  	if( isset($_POST['product_type']) && !empty($_POST['product_type']) ) {
			if ( 'downloadable' == $_POST['product_type'] ) {
				$args['meta_value']    = 'yes';
				$args['meta_key']      = '_downloadable';
			} elseif ( 'virtual' == $_POST['product_type'] ) {
				$args['meta_value']    = 'yes';
				$args['meta_key']      = '_virtual';
			} elseif ( 'variable' == $_POST['product_type'] || 'simple' == $_POST['product_type'] ) {
				$args['tax_query'][] = array(
																		'taxonomy' => 'product_type',
																		'field' => 'slug',
																		'terms' => array(wc_clean($_POST['product_type'])),
																		'operator' => 'IN'
																	);
			} else {
				$args['tax_query'][] = array(
																		'taxonomy' => 'product_type',
																		'field' => 'slug',
																		'terms' => array(wc_clean($_POST['product_type'])),
																		'operator' => 'IN'
																	);
			}
		}
		
		if( isset($_POST['product_cat']) && !empty($_POST['product_cat']) ) {
			$args['tax_query'][] = array(
																		'taxonomy' => 'product_cat',
																		'field'    => 'term_id',
																		'terms'    => array(wc_clean($_POST['product_cat'])),
																		'operator' => 'IN'
																	);
		}
		
		if( isset($_POST['product_taxonomy']) && !empty($_POST['product_taxonomy']) && is_array( $_POST['product_taxonomy'] ) ) {
			foreach( $_POST['product_taxonomy'] as $custom_taxonomy => $taxonomy_id ) {
				if( $taxonomy_id ) {
					$args['tax_query'][] = array(
																				'taxonomy' => $custom_taxonomy,
																				'field'    => 'term_id',
																				'terms'    => array($taxonomy_id),
																				'operator' => 'IN'
																			);
				}
			}
		}
		
		// Vendor Filter
		if( isset($_POST['product_vendor']) && !empty($_POST['product_vendor']) ) {
			$is_marketplace = wcfm_is_marketplace();
			if( $is_marketplace ) {
				if( !wcfm_is_vendor() ) {
					if( $is_marketplace == 'wcpvendors' ) {
						$args['tax_query'][] = array(
																					'taxonomy' => WC_PRODUCT_VENDORS_TAXONOMY,
																					'field' => 'term_id',
																					'terms' => wc_clean($_POST['product_vendor']),
																				);
					} elseif( $is_marketplace == 'wcvendors' ) {
						$args['author'] = $_POST['product_vendor'];
					} elseif( $is_marketplace == 'wcmarketplace' ) {
						$vendor_term = absint( get_user_meta( wc_clean($_POST['product_vendor']), '_vendor_term_id', true ) );
						$args['tax_query'][] = array(
																					'taxonomy' => 'dc_vendor_shop',
																					'field' => 'term_id',
																					'terms' => $vendor_term,
																				);
					} elseif( $is_marketplace == 'dokan' ) {
						$args['author'] = wc_clean($_POST['product_vendor']);
					} elseif( $is_marketplace == 'wcfmmarketplace' ) {
						$args['author'] = wc_clean($_POST['product_vendor']);
					}
				}
			}
		}
		
		// Order by SKU
		if( isset( $_POST['order'] ) && isset( $_POST['order'][0] ) && isset( $_POST['order'][0]['column'] ) && ( $_POST['order'][0]['column'] == 3 ) ) {
			$args['meta_key'] = '_sku';
			$args['orderby']  = 'meta_value';
			$args['order']    = wc_clean($_POST['order'][0]['dir']);
		}
		
		// Order by Price
		if( isset( $_POST['order'] ) && isset( $_POST['order'][0] ) && isset( $_POST['order'][0]['column'] ) && ( $_POST['order'][0]['column'] == 6 ) ) {
			$args['meta_key'] = '_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = wc_clean($_POST['order'][0]['dir']);
		}
		
		// Order by View Count
		if( isset( $_POST['order'] ) && isset( $_POST['order'][0] ) && isset( $_POST['order'][0]['column'] ) && ( $_POST['order'][0]['column'] == 9 ) ) {
			$args['meta_key'] = '_wcfm_product_views';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = wc_clean($_POST['order'][0]['dir']);
		}
		
		// Order by Date
		if( isset( $_POST['order'] ) && isset( $_POST['order'][0] ) && isset( $_POST['order'][0]['column'] ) && ( $_POST['order'][0]['column'] == 10 ) ) {
			$args['orderby']  = 'date';
			$args['order']    = wc_clean($_POST['order'][0]['dir']);
		}
		
		//$args = apply_filters( 'wcfm_products_args', $args );
		
		$wcfm_products_array = get_posts( $args );
		$custom_spma_args = array(
				'post_type'        => 'product',
				'post_status'   => $post_status,
				'posts_per_page' => -1,
				'suppress_filters' => 0,
				'author__in'	   => $users_arr,
				'post_status'      => array('draft', 'pending', 'publish', 'private', 'scheduled' ),
		);
		
		$new_spma_ps = get_posts($custom_spma_args);
		$pro_count = count($new_spma_ps);
		$filtered_pro_count = 0;
		// Get Product Count
		// $current_user_id  = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		// if( !wcfm_is_vendor() ) $current_user_id = 0;
		// $count_products = array();
		// if( isset($_POST['product_status']) && !empty($_POST['product_status']) && ( $_POST['product_status'] != 'any' ) ) {
		// 	$pro_count = wcfm_get_user_posts_count( $current_user_id, 'product', wc_clean($_POST['product_status']) );
		// } else {
		// 	$pro_count = wcfm_get_user_posts_count( $current_user_id, 'product', 'publish' );
		// 	$pro_count += wcfm_get_user_posts_count( $current_user_id, 'product', 'pending' );
		// 	$pro_count += wcfm_get_user_posts_count( $current_user_id, 'product', 'draft' );
		// 	$pro_count += wcfm_get_user_posts_count( $current_user_id, 'product', 'private' );
		// }
		
		// Get Filtered Post Count
		$filtered_pro_count = $pro_count; 
		
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) {
			
			$args['posts_per_page'] = -1;
			$args['offset'] = 0;
			$args['fields'] = 'ids';
			
			$wcfm_products_count_array = get_posts( $args );
			$filtered_pro_count = $pro_count = count( $wcfm_products_count_array );
			
			unset( $args['s'] );
			unset( $args['fields'] );
			
			$search_ids = array();
			$terms      = explode( ',', wc_clean($_POST['search']['value']) );
	
			foreach ( $terms as $term ) {
				if ( is_numeric( $term ) ) {
					$search_ids[] = $term;
				}
	
				// Attempt to get a SKU
				$sku_to_id = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_parent FROM {$wpdb->posts} LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE meta_key='_sku' AND meta_value LIKE %s;", '%' . $wpdb->esc_like( wc_clean( $term ) ) . '%' ) );
				$sku_to_id = array_merge( wp_list_pluck( $sku_to_id, 'ID' ), wp_list_pluck( $sku_to_id, 'post_parent' ) );
	
				if ( ( $sku_to_id != 0 ) && sizeof( $sku_to_id ) > 0 ) {
					$search_ids = array_merge( $search_ids, $sku_to_id );
				}
			}
			
			if( !empty( $search_ids ) ) {
				if( ( !is_array( $args['include'] ) && $args['include'] == '' ) || ( is_array($args['include']) && empty( $args['include'] ) ) ) {
					$args['include'] = $search_ids;
				} elseif( is_array($args['include']) && !empty( $args['include'] ) ) {
					$args['include'] = array_merge( $args['include'], $search_ids );
				}
			
				$wcfm_sku_search_products_array = get_posts( $args );
				
				if( count( $wcfm_sku_search_products_array ) > 0 ) {
					$wcfm_products_array = array_merge( $wcfm_products_array, $wcfm_sku_search_products_array );
					$wcfm_products_array = wcfm_unique_obj_list( $wcfm_products_array );
					$filtered_pro_count += count( $wcfm_products_array );
				}
			}
		}
		
		// Generate Products JSON
		$wcfm_products_json = '';
		$wcfm_products_json = '{
															"draw": ' . wc_clean($_POST['draw']) . ',
															"recordsTotal": ' . $pro_count . ',
															"recordsFiltered": ' . $filtered_pro_count . ',
															"data": ';
		if(!empty($wcfm_products_array)) {
			$index = 0;
			$wcfm_products_json_arr = array();
			foreach($wcfm_products_array as $wcfm_products_single) {
				$the_product = wc_get_product( $wcfm_products_single );
				
				if( !is_a( $the_product, 'WC_Product' ) ) continue;
				
				// Bulk Action Checkbox
				/*if( apply_filters( 'wcfm_is_allow_bulk_edit', true ) && WCFM_Dependencies::wcfmu_plugin_active_check() ) {
					$wcfm_products_json_arr[$index][] =  '<input type="checkbox" class="wcfm-checkbox bulk_action_checkbox_single" name="bulk_action_checkbox[]" value="' . $wcfm_products_single->ID . '" />';
				} else {
					$wcfm_products_json_arr[$index][] =  '';
				}*/
				
				// Thumb
				if( ( ( $wcfm_products_single->post_status != 'publish' ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) || ( apply_filters( 'wcfm_is_allow_edit_products', true ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) ) {
					$wcfm_products_json_arr[$index][] =  '<a href="' . get_wcfm_edit_product_url($wcfm_products_single->ID, $the_product) . '">' . $the_product->get_image( 'thumbnail' ) . '</a>';
				} else {
					$wcfm_products_json_arr[$index][] =  $the_product->get_image( 'thumbnail' );
				}
				
				// Title
				if( ( ( $wcfm_products_single->post_status != 'publish' ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) || ( apply_filters( 'wcfm_is_allow_edit_products', true ) && apply_filters( 'wcfm_is_allow_edit_specific_products', true, $wcfm_products_single->ID ) ) ) {
					$wcfm_products_json_arr[$index][] =  apply_filters( 'wcfm_product_title_dashboard', '<a href="' . get_wcfm_edit_product_url($wcfm_products_single->ID, $the_product) . '" class="wcfm_product_title">' . $wcfm_products_single->post_title . '</a>', $wcfm_products_single->ID );
				} else {
					$wcfm_products_json_arr[$index][] =  apply_filters( 'wcfm_product_title_dashboard', $wcfm_products_single->post_title, $wcfm_products_single->ID );
				}
				
				// SKU
				$product_sku = ( get_post_meta($wcfm_products_single->ID, '_sku', true) ) ? get_post_meta( $wcfm_products_single->ID, '_sku', true ) : '-';
				$wcfm_products_json_arr[$index][] =  apply_filters( 'wcfm_product_sku_dashboard', $product_sku, $wcfm_products_single->ID );
				
				// Status
				if( $wcfm_products_single->post_status == 'publish' ) {
					$wcfm_products_json_arr[$index][] =  '<span class="product-status product-status-' . $wcfm_products_single->post_status . '">' . __( 'Published', 'wc-frontend-manager' ) . '</span>';
				} else {
					if( isset( $wcfmu_products_status[$wcfm_products_single->post_status] ) ) {
						$wcfm_products_json_arr[$index][] =  '<span class="product-status product-status-' . $wcfm_products_single->post_status . '">' . $wcfmu_products_status[$wcfm_products_single->post_status] . '</span>';
					} else {
						$wcfm_products_json_arr[$index][] =  '<span class="product-status product-status-pending">' . __( ucfirst( $wcfm_products_single->post_status ), 'wc-frontend-manager' ) . '</span>';
					}
				}
				
				// Stock
				$stock_status = $the_product->get_stock_status();
				$stock_options = array('instock' => __('In stock', 'wc-frontend-manager'), 'outofstock' => __('Out of stock', 'wc-frontend-manager'), 'onbackorder' => __( 'On backorder', 'wc-frontend-manager' ) );
				if ( array_key_exists( $stock_status, $stock_options ) ) {
					$stock_html = '<span class="'.$stock_status.'">' . $stock_options[$stock_status] . '</span>';
				} else {
					$stock_html = '<span class="instock">' . __( 'In stock', 'woocommerce' ) . '</span>';
				}
		
				// If the product has children, a single stock level would be misleading as some could be -ve and some +ve, some managed/some unmanaged etc so hide stock level in this case.
				if ( $the_product->managing_stock() && ! sizeof( $the_product->get_children() ) ) {
					$stock_html .= ' (' . $the_product->get_stock_quantity() . ')';
				}
				$wcfm_products_json_arr[$index][] =  apply_filters( 'woocommerce_admin_stock_html', $stock_html, $the_product );
				
				// Price
				$wcfm_products_json_arr[$index][] =  $the_product->get_price_html() ? $the_product->get_price_html() : '<span class="na">&ndash;</span>';
				
				// Taxonomies
				$taxonomies = '';
				$pcategories = get_the_terms( $the_product->get_id(), 'product_cat' );
				if( !empty($pcategories) ) {
					$taxonomies .= '<strong>' . __( 'Categories', 'wc-frontend-manager' ) . '</strong>: ';
					$is_first = true;
					foreach($pcategories as $pkey => $pcategory) {
						if( !$is_first ) $taxonomies .= ', ';
						$is_first = false;
						$taxonomies .= '<a style="color: #5B9A68" href="' . get_term_link( $pcategory->term_id ) . '" target="_blank">' . $pcategory->name . '</a>';
					}
				}
				
				// Custom Taxonomies
				if( apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
					$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
					if( !empty( $product_taxonomies ) ) {
						foreach( $product_taxonomies as $product_taxonomy ) {
							if( !in_array( $product_taxonomy->name, array( 'product_cat', 'product_tag', 'wcpv_product_vendors' ) ) ) {
								if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
									// Fetching Saved Values
									$taxonomy_values = get_the_terms( $the_product->get_id(), $product_taxonomy->name );
									if( !empty($taxonomy_values) ) {
										$taxonomies .= "<br /><strong>" . __( $product_taxonomy->label, 'wc-frontend-manager' ) . '</strong>: ';
										$is_first = true;
										foreach($taxonomy_values as $pkey => $ptaxonomy) {
											if( !$is_first ) $taxonomies .= ', ';
											$is_first = false;
											$taxonomies .= '<a style="color: #dd4b39;" href="' . get_term_link( $ptaxonomy->term_id ) . '" target="_blank">' . $ptaxonomy->name . '</a>';
										}
									}
								}
							}
						}
					}
				}
				
				if( !$taxonomies ) $taxonomies = '&ndash;';
				$wcfm_products_json_arr[$index][] =  $taxonomies;
				
				
				
				$vendor_arr = $WCFM->wcfm_vendor_support->wcfm_get_vendor_list();

				
				//$exclude_vendors_html = '<selece>'
				$selected = get_post_meta( $wcfm_products_single->ID, '_spmv_exclude_stores',true );
				ob_start();
				
				 $WCFM->wcfm_fields->wcfm_generate_form_field( 
				 	array("dropdown_vendor_multi" => array( 
				 		'type' => 'select', 
				 		'class' => 'select short dropdown_vendor_multi', 
				 		'options' => $vendor_arr, 
				 		'value' => $selected, 
				 		'attributes' => array( 
				 			'style' => 'width:400px;',
				 			'multiple'=>'multiple',
				 			'data-proid'=> $wcfm_products_single->ID
				 		) 
				 	)
				 ));
				

				$exclude_vendors = ob_get_clean();
				$wcfm_products_json_arr[$index][] =  $exclude_vendors;

				$is_checked = get_post_meta( $wcfm_products_single->ID, '_spmv_all_stores',true );
				$checked = '';
				if($is_checked == 'true')
				{
					$checked = 'checked';
				}
				$wcfm_products_json_arr[$index][] =  '<input type="checkbox" '.$checked.' class="assign_to_verndors wcfm-checkbox bulk_action_checkbox_single" id="assign_to_verndors" name="assign_to_verndors" data-proid="' . $wcfm_products_single->ID . '"  />';
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_products_json_arr) ) $wcfm_products_json .= json_encode($wcfm_products_json_arr);
		else $wcfm_products_json .= '[]';
		$wcfm_products_json .= '
													}';
													
		echo $wcfm_products_json;
		exit;
	}
	
}