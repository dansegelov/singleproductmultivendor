<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       www.idomit.com
 * @since      1.0.0
 *
 * @package    Single_Product_Multivendor
 * @subpackage Single_Product_Multivendor/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Single_Product_Multivendor
 * @subpackage Single_Product_Multivendor/includes
 * @author     idomit <info@idomit.com>
 */
class Single_Product_Multivendor_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'single-product-multivendor',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
