<?php
/*
 * Plugin Name: Q&A
 * Description: Website Q&A section
 * Author: Albert Kuular <albert.kuular@gmail.com>
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