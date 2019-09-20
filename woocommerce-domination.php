<?php
/**
 * Plugin Name:          DOMINATION! Let WooCommerce take control of your dashboard
 * Plugin URI:           https://github.com/claudiosanches/woocommerce-domination
 * Description:          Allows the WooCommerce take the control of your WordPress admin.
 * Author:               Claudio Sanches
 * Author URI:           https://claudiosanches.com
 * Version:              1.2.0
 * License:              GPL-3.0
 * Text Domain:          woocommerce-domination
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      3.7.0
 *
 * DOMINATION! Let WooCommerce take control of your dashboard is free software:
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or any later version.
 *
 * DOMINATION! Let WooCommerce take control of your dashboard is distributed in
 * the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DOMINATION! Let WooCommerce take control of your dashboard.
 * If not, see <https://www.gnu.org/licenses/gpl-3.0.txt>.
 *
 * @package WooCommerce_Domination
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_DOMINATION_VERSION', '1.2.0' );
define( 'WC_DOMINATION_PLUGIN_FILE', __FILE__ );

if ( ! class_exists( 'WC_Domination' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wc-domination.php';

	add_action( 'plugins_loaded', array( 'WC_Domination', 'init' ) );
}
