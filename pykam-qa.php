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

/**
 * URL to the plugin
 */
define('PYKAM_QA_URL', plugin_dir_url( __FILE__ ));

require constant('PYKAM_QA_PATH') . '/vendor/autoload.php';
// require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

add_action( 'plugins_loaded', 'pykam_qa_init' );
function pykam_qa_init() {

	load_plugin_textdomain( 'pykam-qa', false, '/pykam-qa/languages' );

	new \PykamQA\PostType();
	new \PykamQA\MetaBox();
	new \PykamQA\Assets();
	
}