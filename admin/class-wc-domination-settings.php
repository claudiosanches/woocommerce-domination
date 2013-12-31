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
 * @package WC_Domination_Settings
 * @author  Claudio Sanches <contato@claudiosmweb.com>
 */
class WC_Domination_Settings {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var   object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since 1.0.0
	 *
	 * @var   string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		$this->plugin_slug = WC_Domination::get_plugin_slug();
		$this->options_name = WC_Domination::get_options_name();

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Init plugin options form.
		add_action( 'admin_init', array( $this, 'plugin_settings' ) );
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
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'WooCommerce Domination', $this->plugin_slug ),
			__( 'WC Domination', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function display_plugin_admin_page() {
		include_once plugin_dir_path( __FILE__ ) . 'views/admin.php';
	}

	/**
	 * Plugin settings form fields.
	 *
	 * @since 1.0.0
	 *
	 * @return void.
	 */
	public function plugin_settings() {
		$option = $this->options_name;

		// Set Custom Fields section.
		add_settings_section(
			'settings_section',
			__( 'Settings:', $this->plugin_slug ),
			'__return_null',
			$option
		);

		// Person Type option.
		add_settings_field(
			'core_features',
			__( 'Remove Core Posts features:', $this->plugin_slug ),
			array( $this, 'select_element_callback' ),
			$option,
			'settings_section',
			array(
				'menu'        => $option,
				'id'          => 'core_features',
				'description' => __( 'This option controls the visibility of the WordPress native posts.', $this->plugin_slug ),
				'options'     => array(
					'none'     => __( 'Do nothing', $this->plugin_slug ),
					'managers' => __( 'Hidden only to shop managers', $this->plugin_slug ),
					'all'      => __( 'Hidden for all', $this->plugin_slug )
				)
			)
		);

		// Register settings.
		register_setting( $option, $option, array( $this, 'validate_options' ) );
	}

	/**
	 * Checkbox element fallback.
	 *
	 * @since 1.0.0
	 *
	 * @return string Checkbox field.
	 */
	public function checkbox_element_callback( $args ) {
		$menu    = $args['menu'];
		$id      = $args['id'];
		$options = get_option( $menu );

		if ( isset( $options[ $id ] ) ) {
			$current = $options[ $id ];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '0';
		}

		$html = '<input type="checkbox" id="' . $id . '" name="' . $menu . '[' . $id . ']" value="1"' . checked( 1, $current, false ) . '/>';

		if ( isset( $args['label'] ) ) {
			$html .= ' <label for="' . $id . '">' . $args['label'] . '</label>';
		}

		if ( isset( $args['description'] ) ) {
			$html .= '<p class="description">' . $args['description'] . '</p>';
		}

		echo $html;
	}

	/**
	 * Select element fallback.
	 *
	 * @since 1.0.0
	 *
	 * @return string Select field.
	 */
	public function select_element_callback( $args ) {
		$menu    = $args['menu'];
		$id      = $args['id'];
		$options = get_option( $menu );

		// Sets current option.
		if ( isset( $options[ $id ] ) ) {
			$current = $options[ $id ];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}

		$html = sprintf( '<select id="%1$s" name="%2$s[%1$s]">', $id, $menu );
		foreach( $args['options'] as $key => $label ) {
			$key = sanitize_title( $key );

			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
		}
		$html .= '</select>';

		// Displays the description.
		if ( $args['description'] ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		echo $html;
	}

	/**
	 * Valid options.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $input options to valid.
	 *
	 * @return array        validated options.
	 */
	public function validate_options( $input ) {
		$output = array();

		// Loop through each of the incoming options.
		foreach ( $input as $key => $value ) {
			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[ $key ] ) ) {
				$output[ $key ] = woocommerce_clean( $input[ $key ] );
			}
		}

		return $output;
	}
}
