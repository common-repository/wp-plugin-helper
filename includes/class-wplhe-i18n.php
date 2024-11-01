<?php
/**
 * Define the internationalization functionality.
 *
 * @since      1.0.0
 * @package    WPLHE
 * @subpackage WPLHE/includes
 * @author     Grega Radelj <info@grrega.com>
 */
class WPLHE_i18n {

	public function wplhe_load_plugin_textdomain() {
		load_plugin_textdomain(
			'wp-plugin-helper',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

}
