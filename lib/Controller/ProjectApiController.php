<?php
namespace OCA\Projectcreatoraio\Controller;

use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

class ProjectApiController extends Controller {
    /**
     * Summary of index
     * @return DataResponse
     * 
     * @NoCSRFRequired
     */
    public function index(): DataResponse {
        // Logic to handle the index request
        return new DataResponse(['message' => 'Welcome to the Project API']);
    }

    /**
     * Create a new project
     * 
     * @return DataResponse
     * 
     * @NoCSRFRequired
     */
    public function create(): DataResponse {
        // Logic to create a project
        return new DataResponse(['message' => 'Project created']);
    }

    /**
     * Get a project by ID
     * 
     * @param int $id
     * @return DataResponse
     * 
     * @NoCSRFRequired
     */
    public function update(int $id): DataResponse {
        // Logic to update a project by ID
        return new DataResponse(['message' => "Project with ID $id updated"]);
    }

    /**
     * List all projects
     * @return DataResponse
     * 
     * @NoCSRFRequired
     */
    public function list(): DataResponse {
        // Logic to list projects
        return new DataResponse(['message' => 'List of projects']);
    }
}