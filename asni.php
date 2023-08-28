<?php
/**
 * Plugin Name: ASNI - Functionality Plugin
 * Plugin URI: https://www.subarkah.com/asni
 * Description:  WordPress functionality plugin | <a href="/wp-admin/plugin-editor.php">Plugin Editor<a/> | <a href="/asni/create-file/?file_name=">Create File</a> | <a href="/asni/create-class/?class_name=">Create Class</a>
 * Version: 0.1
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Akah Martono
 * Author URI: https://www.subarkah.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
**/

use ASNI\Asni;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASNI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ASNI_CLASS_DIR', ASNI_PLUGIN_DIR . 'class' . DIRECTORY_SEPARATOR  );
define( 'ASNI_PHP_DIR', ASNI_PLUGIN_DIR . 'php' . DIRECTORY_SEPARATOR  );
define( 'ASNI_CSS_DIR', ASNI_PLUGIN_DIR . 'css' . DIRECTORY_SEPARATOR  );
define( 'ASNI_JS_DIR', ASNI_PLUGIN_DIR . 'js' . DIRECTORY_SEPARATOR  );

define( 'ASNI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ASNI_CLASS_URL', ASNI_PLUGIN_URL . 'class/' );
define( 'ASNI_PHP_URL', ASNI_PLUGIN_URL . 'php/' );
define( 'ASNI_CSS_URL', ASNI_PLUGIN_URL . 'css/' );
define( 'ASNI_JS_URL', ASNI_PLUGIN_URL . 'js/' );

require_once( 'autoloader.php' );
Asni::init();
require_once( 'functions.php' );