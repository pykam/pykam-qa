<?php
// Basic test bootstrap for unit tests.
// Load composer autoload.
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define ABSPATH to prevent WordPress-dependent code from exiting
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

require __DIR__ . '/../vendor/autoload.php';

// Provide shims for WordPress functions that unit tests might need
if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		// No-op for unit tests
	}
}

if ( ! function_exists( 'add_meta_box' ) ) {
	function add_meta_box( $id, $title, $callback, $screen = '', $context = 'advanced', $priority = 'default', $callback_args = null ) {
		// No-op for unit tests
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		// No-op for unit tests
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( '_e' ) ) {
	function _e( $text, $domain = 'default' ) {
		echo $text;
	}
}

// Provide a minimal WP_Query shim if WordPress is not available during unit tests.
if ( ! class_exists( 'WP_Query' ) ) {
	class WP_Query {
		public $query_vars = array();
		public function __construct( $args = array() ) {
			$this->query_vars = $args;
		}
	}
}