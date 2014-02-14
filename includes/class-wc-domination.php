<?php
/**
 * WooCommerce Domination.
 *
 * @package WooCommerce_Domination
 * @author  Claudio Sanches <contato@claudiosmweb.com>
 * @license GPL-2.0+
 */

/**
 * Plugin admin class.
 *
 * @package WC_Domination
 * @author  Claudio Sanches <contato@claudiosmweb.com>
 */
class WC_Domination {

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @var   string
	 */
	const VERSION = '1.0.0';

	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @var   string
	 */
	protected static $plugin_slug = 'woocommerce-domination';

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var   object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Initialize the plugin public actions
		$this->init();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
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
	 * Return the plugin slug.
	 *
	 * @since  1.0.0
	 *
	 * @return Plugin slug variable.
	 */
	public static function get_plugin_slug() {
		return self::$plugin_slug;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$domain = self::$plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Initialize the plugin public actions.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function init() {
		if ( self::has_woocommerce_activated() ) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				// Custom admin bar.
				add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 50 );
			}

			// Public scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}

	/**
	 * Test with WooCommerce is activated.
	 *
	 * @since  1.0.0
	 *
	 * @return bool
	 */
	public static function has_woocommerce_activated() {
		if ( function_exists( 'WC' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Public scripts.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			wp_enqueue_style( 'woocommerce-domination-menus', plugins_url( 'assets/css/menus.css', plugin_dir_path( __FILE__ ) ), array(), self::VERSION );
		}
	}

	/**
	 * Custom admin bar.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function admin_bar( $wp_admin_bar ) {
		// Orders.
		$orders_menu_count = '<span class="ab-icon dashicons-list-view"></span><span class="ab-label awaiting-mod pending-count count-0">0</span>';
		$order_menu_title  = __( 'Orders', self::$plugin_slug );
		if ( $order_count = wc_processing_order_count() ) {
			$orders_menu_count = '<span class="ab-icon dashicons-list-view"></span><span class="ab-label awaiting-mod pending-count count-' . $order_count . '">' . number_format_i18n( $order_count ) . '</span>';
			$order_menu_title  = sprintf( _n( '%d order pending', '%d orders pending', $order_count, self::$plugin_slug ), $order_count );
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wc-orders',
				'title' => $orders_menu_count,
				'meta'  => array(
					'title' => $order_menu_title
				),
				'href'  => admin_url( 'edit.php?post_type=shop_order' )
			)
		);

		// Reports.
		$wp_admin_bar->add_node(
			array(
				'id'    => 'wc-reports',
				'title' => '<span class="ab-icon dashicons-chart-area"></span><span class="ab-label">' . __( 'Reports', self::$plugin_slug ) . '</span>',
				'meta'  => array(
					'title' => __( 'view reports', self::$plugin_slug )
				),
				'href'  => admin_url( 'admin.php?page=wc-reports' )
			)
		);

		// General.
		if ( ! is_admin() ) {
			$wp_admin_bar->add_node(
				array(
					'id'     => 'wc-customers-list',
					'parent' => 'site-name',
					'title'  => __( 'Customers', self::$plugin_slug ),
					'href'   => admin_url( 'admin.php?page=wc-customers-list' )
				)
			);
		}
	}
}
