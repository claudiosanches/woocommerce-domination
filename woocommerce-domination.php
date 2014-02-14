<?php
/**
 * WooCommerce Domination.
 *
 * @package   WooCommerce_Domination
 * @author    Claudio Sanches <contato@claudiosmweb.com>
 * @license   GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Domination
 * Plugin URI:        https://github.com/claudiosmweb/woocommerce-domination
 * Description:       Let the WooCommerce take the control of your WordPress admin.
 * Version:           1.0.0-beta
 * Author:            claudiosanches
 * Author URI:        http://claudiosmweb.com/
 * Text Domain:       woocommerce-domination
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/claudiosmweb/woocommerce-domination
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Public.
 */
require_once plugin_dir_path( __FILE__ ) . 'public/class-wc-domination.php';
add_action( 'plugins_loaded', array( 'WC_Domination', 'get_instance' ) );

/**
 * Admin.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'admin/class-wc-domination-admin.php';
	add_action( 'plugins_loaded', array( 'WC_Domination_Admin', 'get_instance' ), 999 );
}
