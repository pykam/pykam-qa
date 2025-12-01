<?php
/*
 * Plugin Name: FAQ
 * Description: Website FAQ section
 * Author: Albert Kuular <albert.kuular@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( defined( 'PYKAM_FAQ_VERSION' ) ) {
	return;
}

/**
 * Plugin version.
 */
define( 'PYKAM_FAQ_VERSION', '1.0.0' );