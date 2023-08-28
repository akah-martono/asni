<?php
/**
 * This file for autoloader of ASNI namespace
 * 
 * @link https://github.com/akah-martono/asni
 * @package ASNI
 * 
 * @author Akah Martono <https://www.subarkah.com>
 * @since 0.1
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(function($class) {
    $ns_prefix = 'ASNI\\';
    if ( substr($class, 0, strlen($ns_prefix))  === $ns_prefix  ) {
        $class_file = substr($class, strlen($ns_prefix));
        $class_file = str_replace("_", '-', $class_file);
        $class_file = strtolower($class_file) . '.php';        
        require_once( ASNI_CLASS_DIR . $class_file );
    }
});