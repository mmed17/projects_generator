<?php

declare(strict_types=1);

namespace OCA\ProjectCreatorAIO\Controller;

use OCA\ProjectCreatorAIO\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;

/**
 * @psalm-suppress UnusedClass
 */
class PageController extends Controller {

	/**
	 * Summary of index
	 * @return TemplateResponse
	 *
	 * @NoCSRFRequired
	 */
	public function index(): TemplateResponse {
		return new TemplateResponse(
			Application::APP_ID,
			'index',
		);
	}
}