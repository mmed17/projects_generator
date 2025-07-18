<?php

declare(strict_types=1);

namespace OCA\ProjectCreatorAIO\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;

/**
 * @psalm-suppress UnusedClass
 */
class ApiController extends OCSController {
	public function index(): DataResponse {
		return new DataResponse(
			['message' => 'Hello world!']
		);
	}
}
