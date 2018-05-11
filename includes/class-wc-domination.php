<?php
/**
 * WooCommerce Domination actions
 *
 * @package WooCommerce_Domination
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce Domination main class.
 */
class WC_Domination {

	/**
	 * Initialize the plugin public actions.
	 */
	public static function init() {
		// Load plugin text domain.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce is installed.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				self::includes();

				if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
					self::admin_includes();
				}

				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 999 );
				add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 999 );
			}
		} else {
			add_action( 'admin_notices', array( __CLASS__, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-correios', false, dirname( plugin_basename( WC_DOMINATION_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private static function includes() {
		include_once dirname( __FILE__ ) . '/class-wc-domination-admin-bar.php';
	}

	/**
	 * Admin includes.
	 */
	private static function admin_includes() {
		include_once dirname( __FILE__ ) . '/admin/class-wc-domination-admin.php';
	}

	/**
	 * Public scripts.
	 */
	public static function enqueue_scripts() {
		wp_enqueue_style( 'woocommerce-domination-menus', plugins_url( 'assets/css/menus.css', WC_DOMINATION_PLUGIN_FILE ), array(), WC_DOMINATION_VERSION );
	}

	/**
	 * WooCommerce fallback notice.
	 */
	public static function woocommerce_missing_notice() {
		/* translators: %s: WooCommerce URL */
		echo '<div class="error"><p><strong>' . esc_html__( 'WooCommerce Domination is inactive.', 'woocommerce-domination' ) . '</strong> ' . wp_kses_post( sprintf( __( 'You must install and active the %s 2.1 or later for the WooCommerce Domination work.', 'woocommerce-domination' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . esc_html__( 'WooCommerce', 'woocommerce-domination' ) . '</a>' ) ) . '</p></div>';
	}
}
