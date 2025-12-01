<?php
/*
 * Plugin Name: Q&A
 * Description: Simple Q&A section
 * Author: Albert Kuular <albert.kuular@gmail.com>
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( defined( 'PYKAM_QA_VERSION' ) ) {
	return;
}

/**
 * Plugin version.
 */
define( 'PYKAM_QA_VERSION', '1.0.0' );

/**
 * Path to the plugin dir.
 */
define( 'PYKAM_QA_PATH', __DIR__ );

add_action( 'plugins_loaded', 'pykam_qa_init' );
function pykam_qa_init() {
	load_plugin_textdomain( 'pykam-qa', false, '/pykam-qa/languages' );
}


include_once constant('PYKAM_QA_PATH') . '/qa-post-type.php';
new PykamQAPostType();