<?php
/**
 * Plugin Name:       Smart Product Table
 * Plugin URI:        https://github.com/maidulcu/smart-product-table
 * Description:       A modern, responsive, and customizable product table for WooCommerce with advanced filtering, bulk actions, and multiple display styles.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Tested up to:      6.7
 * Requires PHP:      7.4
 * Author:            Maidul Islam
 * Author URI:        https://github.com/maidulcu
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smart-product-table
 * Domain Path:       /languages
 * Network:           false
 * WC requires at least: 3.0
 * WC tested up to:   8.0
 *
 * @package Smart_Product_Table
 * @version 1.0.0
 * @author  Maidul Islam
 * @license GPL-2.0+
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/**
 * Define plugin constants (prefix everything).
 */
define( 'SMARTTABLE_VERSION', '1.0.0' );
define( 'SMARTTABLE_MIN_WP', '6.0' );
define( 'SMARTTABLE_MIN_PHP', '7.4' );
define( 'SMARTTABLE_PLUGIN_FILE', __FILE__ );
define( 'SMARTTABLE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SMARTTABLE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMARTTABLE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMARTTABLE_URL', plugins_url( '', SMARTTABLE_PLUGIN_FILE ) );
define( 'SMARTTABLE_ADMIN_ASSETS', SMARTTABLE_URL . '/assets' );

/**
 * Autoloader (Composer). Keep optional but safe.
 */
$smarttable_autoload = SMARTTABLE_PLUGIN_DIR . 'vendor/autoload.php';
if ( file_exists( $smarttable_autoload ) ) {
	require_once $smarttable_autoload;
}



/**
 * Main plugin class
 */
final class Smart_Product_Table {
	
	/**
	 * Plugin version
	 */
	const VERSION = '1.0.0';
	
	/**
	 * Minimum PHP version
	 */
	const MIN_PHP_VERSION = '7.4';
	
	/**
	 * Minimum WordPress version
	 */
	const MIN_WP_VERSION = '5.0';
	
	/**
	 * Plugin instance
	 */
	private static $instance = null;
	
	/**
	 * Get plugin instance
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}
	
	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}
	
	/**
	 * Plugin activation
	 */
	public function activate() {
		// Check PHP version
		if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( __( 'Smart Product Table requires PHP %s or higher.', 'smart-product-table' ), self::MIN_PHP_VERSION ) );
		}
		
		// Check WordPress version
		if ( version_compare( get_bloginfo( 'version' ), self::MIN_WP_VERSION, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( __( 'Smart Product Table requires WordPress %s or higher.', 'smart-product-table' ), self::MIN_WP_VERSION ) );
		}
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
	
	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}
	
	/**
	 * Initialize plugin
	 */
	public function init() {
		// Load text domain
		load_plugin_textdomain( 'smart-product-table', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		// Initialize core plugin
		if ( class_exists( 'SmartTable\\Core\\Plugin' ) ) {
			( new SmartTable\Core\Plugin() )->init();
		}
	}
}

// Initialize plugin
Smart_Product_Table::instance();
