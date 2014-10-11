<?php
/**
 * Plugin Name: WooCommerce Domination
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-domination
 * Description: Allows the WooCommerce take the control of your WordPress admin.
 * Version: 1.1.2
 * Author: Claudio Sanches
 * Author URI: http://claudiosmweb.com/
 * Text Domain: woocommerce-domination
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Domination' ) ) :

/**
 * WooCommerce Domination main class.
 */
class WC_Domination {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.1.2';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin public actions.
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce is installed.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$this->includes();

				if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
					$this->admin_includes();
				}

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
			}
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-domination' );

		load_textdomain( 'woocommerce-domination', trailingslashit( WP_LANG_DIR ) . 'woocommerce-domination/woocommerce-domination-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-domination', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 *
	 * @return void.
	 */
	private function includes() {
		include_once 'includes/class-wc-domination-admin-bar.php';
	}

	/**
	 * Admin includes.
	 *
	 * @return void.
	 */
	private function admin_includes() {
		include_once 'includes/admin/class-wc-domination-admin.php';
	}

	/**
	 * Public scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'woocommerce-domination-menus', plugins_url( 'assets/css/menus.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return  string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p><strong>' . __( 'WooCommerce Domination is inactive.', 'woocommerce-domination' ) . '</strong> ' . sprintf( __( 'You must install and active the %s 2.1 or later for the WooCommerce Domination work.', 'woocommerce-domination' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'woocommerce-domination' ) . '</a>' ) . '</p></div>';
	}
}

add_action( 'plugins_loaded', array( 'WC_Domination', 'get_instance' ), 0 );

endif;
