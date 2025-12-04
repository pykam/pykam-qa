<?php
namespace PykamQA;

use PykamQA\Bootstrapper\AssetsBootstrapper;
use PykamQA\Bootstrapper\MetaBoxBootstrapper;
use PykamQA\Bootstrapper\PostTypeBootstrapper;
use PykamQA\Bootstrapper\TableColumnsBootstrapper;
use PykamQA\Bootstrapper\BootstrapperInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	/**
	 * @var BootstrapperInterface[]
	 */
	private array $bootstrappers;

	/**
	 * @param BootstrapperInterface[] $bootstrappers
	 */
	public function __construct( array $bootstrappers ) {
		$this->bootstrappers = $bootstrappers;
	}

	public static function make(): self {
		return new self(
			array(
				new PostTypeBootstrapper( new PostType() ),
				new MetaBoxBootstrapper( new MetaBox() ),
				new AssetsBootstrapper( new Assets() ),
				new TableColumnsBootstrapper( new TableColumns() ),
			)
		);
	}

	public function boot(): void {
		foreach ( $this->bootstrappers as $bootstrapper ) {
			$bootstrapper->register();
		}
	}
}
