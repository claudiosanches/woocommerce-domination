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
 * @package WC_Domination_Admin
 * @author  Claudio Sanches <contato@claudiosmweb.com>
 */
class WC_Domination_Admin {

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
		$this->plugin_slug = WC_Domination::get_plugin_slug();
		$this->settings    = WC_Domination::get_plugin_options();

		if ( ! WC_Domination::has_woocommerce_activated() ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_fallback_notice' ) );
			return;
		}

		// Plugin settings.
		$this->init_admin_settings();

		// Run options.
		$this->options_switch();

		// Actions only for shop managers and admins.
		$this->init_woocommerce_actions();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since  1.0.0
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
	 * Initialize plugin settings.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function init_admin_settings() {
		if ( apply_filters( 'woocommerce_domination_options_page', true ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'class-wc-domination-settings.php';

			WC_Domination_Settings::init();
		}
	}

	/**
	 * Initialize WooCommerce custom actions only for shop managers.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function init_woocommerce_actions() {
		if ( current_user_can( 'manage_woocommerce' ) ) {

			// Menus.
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 999 );
			add_action( 'menu_order', array( $this, 'menu_order' ), 999 );

			// WooCommerce Post types arguments.
			add_filter( 'woocommerce_register_post_type_shop_order', array( $this, 'custom_post_type_shop_order' ) );
			add_filter( 'woocommerce_register_post_type_shop_coupon', array( $this, 'custom_post_type_shop_coupon' ) );

			// Screen ids.
			add_filter( 'woocommerce_reports_screen_ids', array( $this, 'custom_screen_ids' ) );
			add_filter( 'woocommerce_screen_ids', array( $this, 'custom_screen_ids' ) );

			// Menu highlight.
			add_action( 'admin_head', array( $this, 'menu_highlight' ), 999 );

			// Load admin scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 999 );
		}
	}

	/**
	 * Run plugin options.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function options_switch() {
		if ( 'all' == $this->settings['core_features'] ) {
			// Remove not useful widgets.
			add_action( 'widgets_init', array( $this, 'unregister_posts_widgets' ), 11 );

			// Custom users screens.
			add_filter( 'manage_users_columns', array( $this, 'custom_users_columns' ) );

			// Remove core posts features.
			$this->core_posts();
		} else if (
			'managers' == $this->settings['core_features'] &&
			current_user_can( 'manage_woocommerce' ) &&
			! current_user_can( 'manage_options' )
		) {
			// Remove core posts features.
			$this->core_posts();
		}
	}

	/**
	 * Core posts actions.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function core_posts() {
		// Remove help tabs.
		add_action( 'admin_head', array( $this, 'remove_help_tabs' ) );

		// Remove Welcome Panel.
		remove_action( 'welcome_panel', 'wp_welcome_panel' );

		// Remove dashboard widgets.
		add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );

		// Remove core posts menu.
		add_action( 'admin_menu', array( $this, 'remove_core_posts_menu' ) );
	}

	/**
	 * Remove dashboard widgets.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	}

	/**
	 * Remove widgets.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function unregister_posts_widgets() {
		unregister_widget( 'WP_Widget_Calendar' );
		unregister_widget( 'WP_Widget_Archives' );
		unregister_widget( 'WP_Widget_Links' );
		unregister_widget( 'WP_Widget_Meta' );
		unregister_widget( 'WP_Widget_Search' );
		unregister_widget( 'WP_Widget_Categories' );
		unregister_widget( 'WP_Widget_Recent_Posts' );
		unregister_widget( 'WP_Widget_Recent_Comments' );
		unregister_widget( 'WP_Widget_Tag_Cloud' );
	}

	/**
	 * Remove menu items.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		global $menu;

		$wc_admin_menus = new WC_Admin_Menus;
		$menu[] = array( '', 'read', 'separator-wc-domination1', '', 'wp-not-current-submenu wp-menu-separator' );
		$menu[] = array( '', 'read', 'separator-wc-domination2', '', 'wp-not-current-submenu wp-menu-separator' );

		// Change wc-reports location.
		remove_submenu_page( 'woocommerce', 'wc-reports' );
		add_menu_page( __( 'Reports', $this->plugin_slug ),  __( 'Reports', $this->plugin_slug ) , 'view_woocommerce_reports', 'wc-reports', array( $wc_admin_menus, 'reports_page' ) );

		// Add custom orders menu.
		$orders_menu_name = _x( 'Orders', 'Admin menu name', $this->plugin_slug );
		if ( $order_count = wc_processing_order_count() ) {
			$orders_menu_name .= ' <span class="awaiting-mod update-plugins count-' . $order_count . '"><span class="processing-count">' . number_format_i18n( $order_count ) . '</span></span>';
		}
		add_menu_page( $orders_menu_name, $orders_menu_name, 'manage_woocommerce', 'edit.php?post_type=shop_order' );

		add_menu_page( __( 'Customers', $this->plugin_slug ), __( 'Customers', $this->plugin_slug ), 'manage_woocommerce', 'wc-customers-list', array( $this, 'customers_list_page' ) );
	}

	/**
	 * Remove core posts menu.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function remove_core_posts_menu() {
		remove_menu_page( 'edit.php' );
	}

	/**
	 * Custom WooCommerce screen ids.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $ids Default screen ids.
	 *
	 * @return array      Added new screen ids.
	 */
	public function custom_screen_ids( $ids ) {
		$ids[] = 'toplevel_page_wc-reports';
		$ids[] = 'toplevel_page_wc-customers-list';

		return $ids;
	}

	/**
	 * Custom shop order arguments.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $args Post type arguments.
	 *
	 * @return array       Fixed show_in_menu item.
	 */
	public function custom_post_type_shop_order( $args ) {
		$args['show_in_menu'] = false;

		return $args;
	}

	/**
	 * Custom shop coupon arguments.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $args Post type arguments.
	 *
	 * @return array       Fixed show_in_menu item.
	 */
	public function custom_post_type_shop_coupon( $args ) {
		$args['show_in_menu'] = true;

		return $args;
	}

	/**
	 * Fixed shop order highlight.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function menu_highlight() {
		global $parent_file, $submenu_file, $post_type;

		if ( isset( $post_type ) ) {
			if ( in_array( $post_type, array( 'shop_order', 'shop_coupon' ) ) ) {
				$submenu_file = 'edit.php?post_type=' . esc_attr( $post_type );
				$parent_file  = 'edit.php?post_type=' . esc_attr( $post_type );
			}
		}
	}

	/**
	 * Menu order.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $menu_order Current menu order.
	 *
	 * @return array             New menu order.
	 */
	public function menu_order( $menu_order ) {
		global $submenu;

		// Fix WooCommerce submenus order.
		$submenu_items = array();
		$woocommerce_order = 2;

		foreach ( $submenu['woocommerce'] as $key => $items ) {
			if ( in_array( 'wc-settings', $items ) ) {
				$submenu_items[1] = $items;
			} elseif ( in_array( 'toplevel_page_woocommerce', $items ) ) {
				$submenu_items[0] = $items;
			} else {
				$submenu_items[ $woocommerce_order ] = $items;
			}
			$woocommerce_order++;
		}
		$submenu['woocommerce'] = $submenu_items;

		// Custom menu items order.
		$menu_order = array(
			2 => 'index.php',
			4 => 'separator1',
			6 => 'edit.php?post_type=shop_order',
			8 => 'wc-reports',
			10 => 'wc-customers-list',
			12 => 'separator-wc-domination1',
			14 => 'edit.php?post_type=product',
			16 => 'edit.php?post_type=shop_coupon',
			18 => 'separator-wc-domination2',
			20 => 'edit.php?post_type=page',
			22 => 'upload.php',
			24 => 'edit.php',
			26 => 'edit-comments.php',
			56 => 'separator-woocommerce',
			58 => 'woocommerce',
			59 => 'separator2',
		);

		return $menu_order;
	}

	/**
	 * Remove help tabs.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function remove_help_tabs() {
		$screen = get_current_screen();

		if ( 'dashboard' == $screen->id ) {
			$screen->remove_help_tab( 'help-content' );
		}
	}

	/**
	 * Custom users columns.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $columns Default columns.
	 *
	 * @return array          Removed the posts column.
	 */
	function custom_users_columns( $columns ) {
		unset( $columns['posts'] );

		return $columns;
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since  1.0.0
	 *
	 * @return null Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_style( 'woocommerce-domination-menus', plugins_url( 'assets/css/menus.css', plugin_dir_path( __FILE__ ) ), array(), WC_Domination::VERSION );
	}

	/**
	 * Customers list page.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function customers_list_page() {
		include_once WC()->plugin_path() . '/includes/admin/reports/class-wc-report-customer-list.php';

		$report = new WC_Report_Customer_List();
		$report->output_report();
	}

	/**
	 * Display a notice when WooCommerce is deactivated.
	 *
	 * @since  1.0.0
	 *
	 * @return string Admin notice.
	 */
	public function woocommerce_fallback_notice() {
		echo '<div class="error"><p><strong>' . __( 'WooCommerce Domination is inactive.', $this->plugin_slug ) . '</strong> ' . sprintf( __( 'You must install and active the %s 2.1 or later for the WooCommerce Domination work.', $this->plugin_slug ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', $this->plugin_slug ) . '</a>' ) . '</p></div>';
	}
}
