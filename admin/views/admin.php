<?php
/**
 * WooCommerce Domination.
 *
 * @package WooCommerce_Domination
 * @author  Claudio Sanches <contato@claudiosmweb.com>
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <?php settings_errors(); ?>
    <form method="post" action="options.php">

        <?php
            settings_fields( $this->options_name );
            do_settings_sections( $this->options_name );

            submit_button();
        ?>

    </form>

</div>
