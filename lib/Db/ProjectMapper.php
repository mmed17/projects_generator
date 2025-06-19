<?php

namespace OCA\ProjectCreatorAIO\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\Entity;

class ProjectMapper extends QBMapper {

    public const TABLE_NAME = "custom_projects";
    public function __construct(IDBConnection $db) {
        parent::__construct($db, self::TABLE_NAME, Project::class);
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

        $now = new \DateTime();
        $project->setCreatedAt($now);
        $project->setUpdatedAt($now);

		return $this->insert($project);
	}

    public function search(string $name, int $limit, int $offset) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from(self::TABLE_NAME)
           ->where($qb->expr()->like('name', $qb->createNamedParameter('%' . $name . '%')))
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        
        return $this->findEntities($qb);
    }

    public function list() {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from(self::TABLE_NAME)
           ->orderBy('created_at', 'DESC');
        
        return $this->findEntities($qb);
    }
}