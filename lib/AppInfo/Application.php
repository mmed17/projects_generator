<?php

namespace OCA\Projectcreatoraio\AppInfo;

use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;

use OCA\Projectcreatoraio\Controller\ProjectApiController;

use OCP\IRequest;

class Application extends App implements IBootstrap {
    public const APP_ID = 'projectcreatoraio';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }
	
	public function register(IRegistrationContext $context): void {
		$container = $this->getContainer();
		$container->registerService('ProjectApiController', 
			fn($c) => new ProjectApiController(
				self::APP_ID,
				$c->query(IRequest::class)
			)
		);
	}

	public function boot(IBootContext $context): void {
		$context->registerController(ProjectApiController::class);
	}
}