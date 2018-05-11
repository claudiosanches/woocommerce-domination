<?php
/**
 * Admin bar actions
 *
 * @package WooCommerce_Domination
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce Domination main class.
 */
class WC_Domination {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.1.5';

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
		// Load plugin text domain.
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
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-correios', false, dirname( plugin_basename( WC_DOMINATION_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		include_once dirname( __FILE__ ) . '/class-wc-domination-admin-bar.php';
	}

	/**
	 * Admin includes.
	 */
	private function admin_includes() {
		include_once dirname( __FILE__ ) . '/admin/class-wc-domination-admin.php';
	}

	/**
	 * Public scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'woocommerce-domination-menus', plugins_url( 'assets/css/menus.css', WC_DOMINATION_PLUGIN_FILE ), array(), self::VERSION );
	}

	/**
	 * WooCommerce fallback notice.
	 */
	public function woocommerce_missing_notice() {
		/* translators: %s: WooCommerce URL */
		echo '<div class="error"><p><strong>' . esc_html__( 'WooCommerce Domination is inactive.', 'woocommerce-domination' ) . '</strong> ' . wp_kses_post( sprintf( __( 'You must install and active the %s 2.1 or later for the WooCommerce Domination work.', 'woocommerce-domination' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . esc_html__( 'WooCommerce', 'woocommerce-domination' ) . '</a>' ) ) . '</p></div>';
	}
}
