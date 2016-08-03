<?php
/**
 * Plugin Name: Aaron Speer - Modern Tribe Trial Backend Project
 * Description: Provides a front-end form for submitting websites URLs as a Custom Post Type.
 * Version: 1.0
 * Author: Aaron Speer
 * Text Domain: as-mt-trial
 * License: GPLv2 or later
 */

// the main plugin class
require_once dirname( __FILE__ ) . '/inc/setup.php';

AS_Setup::get_instance();

register_activation_hook( __FILE__, array( 'AS_Setup', 'init' ) );