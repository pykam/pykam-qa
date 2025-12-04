<?php
namespace PykamQA\Bootstrapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface BootstrapperInterface {

	public function register(): void;
}
