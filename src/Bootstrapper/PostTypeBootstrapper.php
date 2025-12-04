<?php
namespace PykamQA\Bootstrapper;

use PykamQA\PostType;
use PykamQA\Bootstrapper\BootstrapperInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostTypeBootstrapper implements BootstrapperInterface {

	public function __construct(
		private PostType $postType
	) {
	}

	public function register(): void {
		$this->postType->register();
	}
}
