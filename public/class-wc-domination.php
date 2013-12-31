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
	 * Plugin version, used for cache-busting of style and script file references.
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
	 * Plugin options name.
	 *
	 * @since 1.0.0
	 *
	 * @var   string
	 */
	protected static $options_name = 'woocommerce_domination';

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

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Plugin actions.
		$this->public_actions();

		// Front-end actions.
		$this->front_end_actions();

		// Public scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
	 * Return the plugin options name.
	 *
	 * @since  1.0.0
	 *
	 * @return Plugin options name variable.
	 */
	public static function get_options_name() {
		return self::$options_name;
	}

	/**
	 * Get plugin options.
	 *
	 * @since  1.0.0
	 *
	 * @return array Plugin options.
	 */
	public static function get_plugin_options() {
		$options = get_option( self::get_options_name() );

		return apply_filters( 'woocommerce_domination_options', $options );
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since  1.0.0
	 *
	 * @param  boolean $network_wide True if WPMU superadmin uses
	 *                               "Network Activate" action, false if
	 *                               WPMU is disabled or plugin is
	 *                               activated on an individual blog.
	 *
	 * @return void
	 */
	public static function activate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();
			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since  1.0.0
	 *
	 * @param  boolean $network_wide True if WPMU superadmin uses
	 *                               "Network Deactivate" action, false if
	 *                               WPMU is disabled or plugin is
	 *                               deactivated on an individual blog.
	 *
	 * @return void
	 */
	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_deactivate();
				}

				restore_current_blog();
			} else {
				self::single_deactivate();
			}
		} else {
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $blog_id ID of the new blog.
	 *
	 * @return void
	 */
	public function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since  1.0.0
	 *
	 * @return array|false The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {
		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since 1.0.0
	 */
	private static function single_activate() {
		add_option( self::get_options_name(), array( 'core_features' => 'none' ) );
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since 1.0.0
	 */
	private static function single_deactivate() {
		delete_option( self::get_options_name() );
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
	 * Initialize front end actions.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function front_end_actions() {
		if ( self::has_woocommerce_activated() ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-domination-front.php';

			WC_Domination_Front::init();
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
	 * Do public actions.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function public_actions() {
		$settings = self::get_plugin_options();

		if ( current_user_can( 'manage_woocommerce' ) ) {
			// Custom admin bar.
			add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 50 );
		}

		// Remove posts from admin bar.
		if ( 'all' == $settings['core_features'] ) {
			add_action( 'admin_bar_menu', array( $this, 'remove_posts_from_admin_bar' ), 999 );
		} else if (
			'managers' == $settings['core_features'] &&
			current_user_can( 'manage_woocommerce' ) &&
			! current_user_can( 'manage_options' )
		) {
			add_action( 'admin_bar_menu', array( $this, 'remove_posts_from_admin_bar' ), 999 );
		}
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
	 * Remove posts from admin bar.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function remove_posts_from_admin_bar( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'new-post' );
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
		$orders_menu_count = '<span class="ab-icon"></span><span class="ab-label awaiting-mod pending-count count-0">0</span>';
		$order_menu_title  = __( 'Orders', self::$plugin_slug );
		if ( $order_count = wc_processing_order_count() ) {
			$orders_menu_count = '<span class="ab-icon"></span><span class="ab-label awaiting-mod pending-count count-' . $order_count . '">' . number_format_i18n( $order_count ) . '</span>';
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
				'title' => '<span class="ab-icon"></span><span class="ab-label">' . __( 'Reports', self::$plugin_slug ) . '</span>',
				'meta'  => array(
					'title' => __( 'view reports', self::$plugin_slug )
				),
				'href'  => admin_url( 'admin.php?page=wc-reports' )
			)
		);

		// General.
		if ( ! is_admin() ) {
			$wp_admin_bar->add_menu(
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
