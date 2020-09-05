<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.idomit.com
 * @since      1.0.0
 *
 * @package    Single_Product_Multivendor
 * @subpackage Single_Product_Multivendor/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Single_Product_Multivendor
 * @subpackage Single_Product_Multivendor/includes
 * @author     idomit <info@idomit.com>
 */
class Single_Product_Multivendor {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Single_Product_Multivendor_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SINGLE_PRODUCT_MULTIVENDOR_VERSION' ) ) {
			$this->version = SINGLE_PRODUCT_MULTIVENDOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'single-product-multivendor';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Single_Product_Multivendor_Loader. Orchestrates the hooks of the plugin.
	 * - Single_Product_Multivendor_i18n. Defines internationalization functionality.
	 * - Single_Product_Multivendor_Admin. Defines all hooks for the admin area.
	 * - Single_Product_Multivendor_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-single-product-multivendor-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-single-product-multivendor-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-single-product-multivendor-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-single-product-multivendor-public.php';

		$this->loader = new Single_Product_Multivendor_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Single_Product_Multivendor_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Single_Product_Multivendor_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		global $current_user;
				
		$plugin_admin = new Single_Product_Multivendor_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_filter('acf/load_field/name=wcfmmp_multi_store',$plugin_admin, 'custom_field_product_multi_vendor');
		$this->loader->add_action( 'acf/save_post', $plugin_admin , 'idm_custom_field_product_multi_vendor_save' );
		$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin , 'idm_custom_field_product_multi_vendor_save' );
		$this->loader->add_filter( 'wcfm_query_vars',$plugin_admin , 'wcfmcsm_query_vars', 50 );
		$this->loader->add_filter( 'wcfm_endpoints_slug', $plugin_admin , 'wcfm_custom_menus_endpoints_slug' );
		$this->loader->add_filter( 'wcfm_menus', $plugin_admin , 'wcfmcsm_wcfm_menus', 20 );
		$this->loader->add_action( 'wcfm_load_views',$plugin_admin , 'wcfm_csm_load_views', 50 );
		$this->loader->add_action( 'before_wcfm_load_views',$plugin_admin , 'wcfm_csm_load_views', 50 );
		$this->loader->add_action( 'init',$plugin_admin, 'wcfmcsm_init', 50 );

		// load products via ajax for datatable by vgotweb
		$this->loader->add_action( 'wp_ajax_spmv_ajax_controller',$plugin_admin, 'processing' );
		$this->loader->add_action( 'wp_ajax_nopriv_spmv_ajax_controller',$plugin_admin, 'processing' );

		$this->loader->add_action('save_post_product',$plugin_admin, 'save_author_meta_as_metafield', 10, 3);


		// Assign exclude stores via ajax to product by vgotweb
		$this->loader->add_action( 'wp_ajax_spmv_exclude_stores',$plugin_admin, 'exclude_store_from_products' );
		$this->loader->add_action( 'wp_ajax_nopriv_spmv_exclude_stores',$plugin_admin, 'exclude_store_from_products' );
		
		// Assign all stores via ajax to product by vgotweb
		$this->loader->add_action( 'wp_ajax_spmv_assign_all_stores',$plugin_admin, 'assign_all_stores_to_products' );
		$this->loader->add_action( 'wp_ajax_nopriv_spmv_assign_all_stores',$plugin_admin, 'assign_all_stores_to_products' );

		//Change arguments to display products on vendor side
		//$this->loader->add_filter( 'wcfm_products_args', $plugin_admin , 'wcfmcsm_wcfm_show_products', 20 );
		
		

		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Single_Product_Multivendor_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Single_Product_Multivendor_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
