<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin Admin Bar class.
 */
class WC_Domination_Admin_Bar {

	/**
	 * Customize the admin bar.
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 50 );
	}

	/**
	 * Custom admin bar.
	 *
	 * @return void
	 */
	public function admin_bar( $wp_admin_bar ) {
		// Orders.
		$orders_menu_count = '<span class="ab-icon dashicons-list-view"></span><span class="ab-label awaiting-mod pending-count count-0">0</span>';
		$order_menu_title  = __( 'Orders', 'woocommerce-domination' );
		if ( $order_count = wc_processing_order_count() ) {
			$orders_menu_count = '<span class="ab-icon dashicons-list-view"></span><span class="ab-label awaiting-mod pending-count count-' . $order_count . '">' . number_format_i18n( $order_count ) . '</span>';
			$order_menu_title  = sprintf( _n( '%d order pending', '%d orders pending', $order_count, 'woocommerce-domination' ), $order_count );
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
				'title' => '<span class="ab-icon dashicons-chart-area"></span><span class="ab-label">' . __( 'Reports', 'woocommerce-domination' ) . '</span>',
				'meta'  => array(
					'title' => __( 'view reports', 'woocommerce-domination' )
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
					'title'  => __( 'Customers', 'woocommerce-domination' ),
					'href'   => admin_url( 'admin.php?page=wc-customers-list' )
				)
			);
		}
	}
}

new WC_Domination_Admin_Bar();
