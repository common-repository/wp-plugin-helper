<?php
/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    WPLHE
 * @subpackage WPLHE/includes
 * @author     Grega Radelj <info@grrega.com>
 */
class WPLHE_Activator {

	public static function wplhe_activate() {
		add_option('wplhe_plugin_notes','');
		update_option('wplhe_version',WPLHE_VERSION);
	}

}
