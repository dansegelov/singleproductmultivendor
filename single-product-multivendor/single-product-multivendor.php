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





/*add_action( 'pre_get_posts', function( $q )
		{
				//print_r($q);
		    if( $title = $q->get( 'author' ) )
		    {
		        add_filter( 'get_meta_sql', function( $sql ) use ( $title )
		        {
		            global $wpdb;

		            //print_r($sql);
		            //$input = preg_replace('AND wp_posts.post_author IN ('.$title.')','', $sql);
		            // Only run once:
		            static $nr = 0; 
		            if( 0 != $nr++ ) return $sql;

		            // Modified WHERE
		            $sql['where'] = sprintf(
		                " AND ( %s OR %s ) ",
		                $wpdb->prepare( "{$wpdb->posts}.post_author = %s", $title),
		                mb_substr( $sql['where'], 5, mb_strlen( $sql['where'] ) )
		            );
		            //print_r($sql);
		            return $sql;
		        });
		    }
		});*/

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
