<?php
/**
 * Plugin Name: Aaron Speer - Modern Tribe Trial Backend Project
 * Description: Provides a front-end form for submitting websites URLs as a Custom Post Type.
 * Version: 1.0
 * Author: Aaron Speer
 * Text Domain: as-mt-trial
 * License: GPLv2 or later
 */

// Include the setup class.
require_once dirname(__FILE__) . '/inc/class-setup.php';

// Include the metaboxes class.
require_once dirname(__FILE__) . '/inc/class-metaboxes.php';

// Include the access control class.
require_once dirname(__FILE__) . '/inc/class-access-control.php';

AS_Setup::get_instance();

register_activation_hook( __FILE__, array( 'AS_Setup', 'init' ) );