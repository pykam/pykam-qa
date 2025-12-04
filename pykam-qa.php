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

add_action( 'plugins_loaded', 'pykam_qa_init' );

/**
 * Bootstraps the plugin after WordPress loads all plugins.
 *
 * @return void
 */
function pykam_qa_init() {

	load_plugin_textdomain( 'pykam-qa', false, '/pykam-qa/languages' );

	new \PykamQA\PostType();
	new \PykamQA\MetaBox();
	new \PykamQA\Assets();
	new \PykamQA\TableColumns();
	
}

/**
 * Initializes and outputs PykamQA component
 *
 * @param int $post_id Attached post ID. Use 0 for the current post
 * @param int $count Maximum number of values to return. 0 = unlimited
 * @return void
 */
function pykam_qa(int $post_id = 0, int $count = 0): void
{
	$pykam_qa = new \PykamQA\PykamQA();
	$pykam_qa->print();
}