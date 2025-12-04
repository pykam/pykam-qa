<?php
namespace PykamQA\Bootstrapper;

use PykamQA\MetaBox;
use PykamQA\Bootstrapper\BootstrapperInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MetaBoxBootstrapper implements BootstrapperInterface {

	public function __construct(
		private MetaBox $metabox
	) {
	}

	public function register(): void {
		$this->metabox->register();
	}
}
