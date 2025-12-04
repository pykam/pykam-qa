<?php
namespace PykamQA\Bootstrapper;

use PykamQA\TableColumns;
use PykamQA\Bootstrapper\BootstrapperInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TableColumnsBootstrapper implements BootstrapperInterface {

	public function __construct(
		private TableColumns $tableColumns
	) {
	}

	public function register(): void {
		$this->tableColumns->register();
	}
}
