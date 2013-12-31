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
 * @package WooCommerce_Domination
 * @author  Claudio Sanches <contato@claudiosmweb.com>
 */
class WooCommerce_Domination {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {

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
}
