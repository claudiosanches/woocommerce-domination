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

		if ( ! WC_Domination::has_woocommerce_activated() ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_fallback_notice' ) );
			return;
		}

		// Initialize plugin actions.
		$this->init();
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
	 * Initialize WooCommerce custom actions only for shop managers.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function init() {
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
	 * Remove menu items.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		global $menu;

		if ( class_exists( 'WC_Admin_Menus' ) ) {
			$wc_admin_menus = new WC_Admin_Menus;
			$menu[] = array( '', 'read', 'separator-wc-domination1', '', 'wp-not-current-submenu wp-menu-separator' );
			$menu[] = array( '', 'read', 'separator-wc-domination2', '', 'wp-not-current-submenu wp-menu-separator' );

			// Add custom orders menu.
			$orders_menu_name = _x( 'Orders', 'Admin menu name', $this->plugin_slug );
			if ( $order_count = wc_processing_order_count() ) {
				$orders_menu_name .= ' <span class="awaiting-mod update-plugins count-' . $order_count . '"><span class="processing-count">' . number_format_i18n( $order_count ) . '</span></span>';
			}
			add_menu_page( $orders_menu_name, $orders_menu_name, 'manage_woocommerce', 'edit.php?post_type=shop_order', '', 'dashicons-list-view' );

			// Change wc-reports location.
			remove_submenu_page( 'woocommerce', 'wc-reports' );
			add_menu_page( __( 'Reports', $this->plugin_slug ),  __( 'Reports', $this->plugin_slug ) , 'view_woocommerce_reports', 'wc-reports', array( $wc_admin_menus, 'reports_page' ), 'dashicons-chart-area' );

			// Add customers menu.
			add_menu_page( __( 'Customers', $this->plugin_slug ), __( 'Customers', $this->plugin_slug ), 'manage_woocommerce', 'wc-customers-list', array( $this, 'customers_list_page' ), 'dashicons-groups' );
		}
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
		$args['menu_icon']    = 'dashicons-tag';

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
