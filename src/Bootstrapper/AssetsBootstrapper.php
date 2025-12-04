<?php
namespace PykamQA\Bootstrapper;

use PykamQA\Assets;
use PykamQa\Bootstrapper\BootstrapperInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AssetsBootstrapper implements BootstrapperInterface {

	public function __construct(
		private Assets $assets
	) {
	}

	public function register(): void {
		$this->assets->register();
	}
}
