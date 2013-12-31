<?php
/**
 * WooCommerce Domination.
 *
 * @package WooCommerce_Domination
 * @author  Claudio Sanches <contato@claudiosmweb.com>
 * @license GPL-2.0+
 */

/**
 * Plugin front class.
 *
 * @package WC_Domination_Front
 * @author  Claudio Sanches <contato@claudiosmweb.com>
 */
class WC_Domination_Front {

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
		$this->wp_head_cleanup();
	}

	/**
	 * Initialize this class.
	 *
	 * @since  1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function init() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Clears the head.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function wp_head_cleanup() {
		if ( is_admin() ) {
			return;
		}

		$settings = WC_Domination::get_plugin_options();

		if ( 'all' == $settings['core_features'] ) {
			// Post and comment feeds.
			remove_action( 'wp_head', 'feed_links', 2 );

			// Category feeds.
			remove_action( 'wp_head', 'feed_links_extra', 3 );

			// EditURI link.
			remove_action( 'wp_head', 'rsd_link' );

			// Windows live writer.
			remove_action( 'wp_head', 'wlwmanifest_link' );

			// Index link.
			remove_action( 'wp_head', 'index_rel_link' );

			// Previous link.
			remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );

			// Start link.
			remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );

			// Links for adjacent posts.
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		}
	}
}
