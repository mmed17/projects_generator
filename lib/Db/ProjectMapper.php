<?php

namespace OCA\ProjectCreatorAIO\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\Entity;

class ProjectMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'custom_projects', Project::class);
    }

    public function createProject(
        string $name,
        string $number,
        int    $type,
        string $address,
        string $description,
        string $ownerId,
        string $circleId,
        string $boardId,
        string $folderName,
    ) {

		$project = new Project();
        
        $project->setName($name);
        $project->setNumber($number);
        $project->setType($type);
        $project->setAddress($address);
        $project->setDescription($description);
        $project->setOwnerId($ownerId);
        $project->setCircleId($circleId);
        $project->setBoardId($boardId);
        $project->setFolderName($folderName);

		return $this->insert($project);
	}
}