<?php
/**
 * WP Plugin Helper by Grega Radelj
 *
 * @link              https://grrega.com/plugins/free/wp-plugin-helper
 * @since             1.0.0
 * @package           WPLHE
 *
 * @wordpress-plugin
 * Plugin Name:       WP Plugin Helper
 * Plugin URI:        https://grrega.com/plugins/free/wp-plugin-helper
 * Description:       Add notes to plugins on the plugin page and display WordPress, WooCommerce and PHP compatibility.
 * Version:           1.1.1
 * Author:            Grega Radelj
 * Author URI:        https://grrega.com/
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.txt
 * Text Domain:       wp-plugin-helper
 * Domain Path:       /languages 
 *
 * Copyright 2018 Grrega.com  (email : info@grrega.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WPLHE_VERSION', '1.1.1' );



/**
 * The code that runs during plugin activation.
 */
function activate_wplhe() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wplhe-activator.php';
	WPLHE_Activator::wplhe_activate();
}

register_activation_hook( __FILE__, 'activate_wplhe' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wplhe.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wplhe() {
	if(is_admin()){
		$plugin = new WPLHE();
		$plugin->wplhe_run();
	}

}
run_wplhe();
