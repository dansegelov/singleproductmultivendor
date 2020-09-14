<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.idomit.com
 * @since             1.0.0
 * @package           Single_Product_Multivendor
 *
 * @wordpress-plugin
 * Plugin Name:       Single Product Multivendor
 * Plugin URI:        www.idomit.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            idomit
 * Author URI:        www.idomit.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       single-product-multivendor
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SINGLE_PRODUCT_MULTIVENDOR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-single-product-multivendor-activator.php
 */
function activate_single_product_multivendor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-single-product-multivendor-activator.php';
	Single_Product_Multivendor_Activator::activate();
}

add_filter( 'woocommerce_shortcode_products_query', 'htdat_woocommerce_shortcode_products_query', 60, 2 );
function htdat_woocommerce_shortcode_products_query( $query_args, $attributes ) {

	//print_r($query_args);
	//print_r($attributes);
	if ($attributes['author']) {
		$user_id = $attributes['author'];
	}
	unset($attributes['author']);

	if ($query_args['author']) {
		$user_id = $query_args['author'];
	}
	unset($query_args['author']);

			if(empty($meta_query))
			{
				$meta_query = array();
			} 
	$query_args['meta_query'] = array(
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
			

    
  //print_r($query_args);
  //print_r($attributes);
  return $query_args;
}
/*function woocommerce_shortcode_products_orderby( $args ) {

	print_r($args);
    $user_id = $args['store'];
				//unset($args['store']);

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
			

    return $args;
}*/


/*add_action( 'woocommerce_product_query', 'so_27975262_product_query' ); 

function so_27975262_product_query($q)
{
	if(is_admin())
	{
		return $q;
	}
	$store_user = get_user_by('login', $q->get( 'store' ));
	//print_r($store_user);
	if ( $store_user->ID ) {
		$store_id = $store_user->ID;
	}
	else
	{
		return $q;
	}
	$meta_query = $q->get( 'meta_query' ); 

	$meta_query[] = 
        array (
         'key' => '_spmv_exclude_stores',
         'value' => '.*;s:[0-9]+:"'.$store_id.'";.*',
         'compare' => 'NOT REGEXP',
        );
      
	$meta_query[] = 
				   array(
		        'key' => '_spmv_all_stores',
		        'value' => 'true',
		        'compare' => '='
		    );
		 
	$q->set( 'meta_query', $meta_query );
	//$q->set( 'store',0 );
		add_filter( 'posts_where', function( $sql, $query ) use ( $store_id )
		        {
		            global $wpdb;
		        	 
		        		if(! is_admin() && $query->is_main_query() && $query->query_vars['post_type'] == 'product')
		        		{
		        			//echo $sql;
		        			static $nr = 0; 
				            if( 0 != $nr++ ) return $sql;

				            // Modified WHERE
				            $sql = sprintf(
				                " AND ( %s OR %s ) ",
				                $wpdb->prepare( "{$wpdb->posts}.post_author = %d", $store_id),
				                mb_substr( $sql, 5, mb_strlen( $sql ) )
				            );

		        			$sql = str_replace('AND (wp_posts.post_author = 11)','', $sql);
				            //echo $sql;
		        		}
		            

		            return $sql;
		        },20,2);
	//print_r($q);
}*/

//add_action( 'elementor/query/my_custom_filter', 'change_query_for_products');
add_action( 'pre_get_posts','change_query_for_products',10);


function change_query_for_products( $q )
{

		//print_r($q);
		
		if($q->get( 'post_type' ) != 'product')
			return $q;
		
		
		if(!$q->is_archive)
			return $q;

		if(in_array('administrator',  wp_get_current_user()->roles)) {
			return $q;
		}
		if($q->get( 'author' ))
		{
			//$store_id = $q->get( 'author' );
			$store_id = 'author';
		}

		if($q->get( 'store' ))
		{
			 	//session_start();
			return $q;
			/*$store_user = get_user_by('login', $q->get( 'store' ));
			//print_r($store_user);
			if ( $store_user->ID ) {
				//$store_id = $store_user->ID;
				//$_SESSION['access_store'] = $store_id;
				return $q;
			}
			else
			{
				return $q;
			}*/

			//print_r($q);
			$meta_query = $q->get( 'meta_query' );

			if(empty($meta_query))
			{
				$meta_query = array();
			} 

				/*$query_args = array(
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
			*/
			$meta_query[] = 
		        array (
		         'key' => '_spmv_exclude_stores',
		         'value' => '.*;s:[0-9]+:"'.$store_id.'";.*',
		         'compare' => 'NOT REGEXP',
		        );
		      
			$meta_query[] = 
						   array(
				        'key' => '_spmv_all_stores',
				        'value' => 'true',
				        'compare' => '='
				    );
				 
			$q->set( 'meta_query', $meta_query );
			$q->set( 'tax_query', array() );
			
			
		}
		//print_r($q);
		if( $store_id)
	  {
	      add_filter( 'posts_where', function( $sql, $query ) use ( $store_id )
	      {
	          global $wpdb;
	          //print_r($query);
	      	if($store_id != 'author')
	      	{
	      		static $nr = 0; 
				            if( 0 != $nr++ ) return $sql;

				            // Modified WHERE
				            $sql = sprintf(
				                " AND ( %s OR %s ) ",
				                $wpdb->prepare( "{$wpdb->posts}.post_author = %d", $store_id),
				                mb_substr( $sql, 5, mb_strlen( $sql ) )
				            );

		        			$sql = str_replace('AND (wp_posts.post_author = '.$store_id.')','', $sql);
		        			//$sql = str_replace('( wp_posts.ID NOT IN ( SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id IN (7) ) ) AND','', $sql);
	      	}
	      	else
	      	{
	      	 
	      		if($query->query_vars['post_type'] == 'product')
	      		{
	      			//echo 'test';
	      			$sql = preg_replace('/AND wp_posts.post_author IN/','OR wp_posts.post_author IN', $sql);
	      		}
	      	}
	        //print_r($sql); 
	      	//print_r($wpdb->queries);
	          return $sql;
	      },10,2);
	  }
	//}

}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-single-product-multivendor-deactivator.php
 */
function deactivate_single_product_multivendor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-single-product-multivendor-deactivator.php';
	Single_Product_Multivendor_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_single_product_multivendor' );
register_deactivation_hook( __FILE__, 'deactivate_single_product_multivendor' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-single-product-multivendor.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_single_product_multivendor() {

	$plugin = new Single_Product_Multivendor();
	$plugin->run();

}
run_single_product_multivendor();
