<?php
/**
 * Plugin Name: Aaron Speer - Modern Tribe Trial Backend Project
 * Description: Provides a front-end form for submitting websites URLs as a Custom Post Type.
 * Version: 1.0
 * Author: Aaron Speer
 * Text Domain: as-mt-trial
 * License: GPLv2 or later
 */

define( 'AS_MT_PLUGIN_DIR', plugin_dir_path(__FILE__) );
define( 'AS_MT_PLUGIN_URI', plugin_dir_url(__FILE__) );

// Include the main class.
require_once dirname( __FILE__ ) . '/inc/class-main.php';

// Include the metaboxes class.
require_once dirname(__FILE__) . '/inc/class-metaboxes.php';

// Include the json-api class.
require_once dirname(__FILE__) . '/inc/class-json-api.php';

AS_Main::get_instance();

register_activation_hook( __FILE__, array( 'AS_Main', 'init' ) );
register_deactivation_hook( __FILE__, array( 'AS_Main', 'teardown' ) );