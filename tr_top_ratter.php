<?php
/**
 * @package tr_top_ratter
 * @version 0.6.4
 */
/*
 * Plugin Name: tr_top_ratter
 * Description: EVE online related plugin. This plugin gathers information about how much isk (in corp tax) corporation members have gathered from ratting within specific time period. 
 * Author: Ugis Varslavans
 * Author URI:        https://evewho.com/pilot/Judge07
 * Version: 0.6.21
 * License: GPL-2.0 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, then abort execution.
if (! defined ( 'WPINC' )) {
	die ();
}

/**
 * Include the core class responsible for loading all necessary components of the plugin.
 */
require_once plugin_dir_path ( __FILE__ ) . 'includes/class-top-ratter.php';

/**
 * Instantiates the top_Ratter class and then
 * calls its run method officially starting up the plugin.
 */
function run_top_ratter() {
	$tr = new Top_Ratter ();
	$tr->run ();
}

// Call the above function to begin execution of the plugin.
run_top_ratter ();

// log errors on plugin activation.
register_activation_hook ( __FILE__, 'my_activation_func' );
function my_activation_func() {
	file_put_contents ( __DIR__ . '/my_loggg.txt', ob_get_contents () );
}
?>