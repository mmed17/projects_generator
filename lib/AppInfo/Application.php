<?php

namespace OCA\ProjectCreatorAIO\AppInfo;

use OCA\ProjectCreatorAIO\Dashboard\ProjectWidget;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
    public const APP_ID = 'projectcreatoraio';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }
	
	public function register(IRegistrationContext $context): void {
		$context->registerDashboardWidget(ProjectWidget::class);
	}

	public function boot(IBootContext $context): void {}
}