<?php

namespace OCA\Projectcreatoraio\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCA\Projectcreatoraio\Controller\ProjectApiController;
use OCA\Circles\CirclesManager;
use OCA\Circles\Service\FederatedUserService;
use OCA\Deck\Service\BoardService;
use OCP\Files\IRootFolder;
use OCP\Share\IManager;
use OCA\Deck\Service\ShareService;

class Application extends App implements IBootstrap {
    public const APP_ID = 'projectcreatoraio';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }
	
	public function register(IRegistrationContext $context): void {
		$container = $this->getContainer();

        $container->registerService('project_api', 
			fn($c): ProjectApiController => new ProjectApiController(
				appName: $c->get('AppName'),
				request: $c->get('Request'),
				userSession: $c->get('UserSession'),
				circlesManager: $c->query(CirclesManager::class),
				shareManager: $c->query(IManager::class),
				boardService: $c->query(BoardService::class),
				rootFolder: $c->query(IRootFolder::class),
				federatedUserService: $c->query(FederatedUserService::class),
			)
		);
	}

	public function boot(IBootContext $context): void {}
}