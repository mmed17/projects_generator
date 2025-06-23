<?php

namespace OCA\ProjectCreatorAIO\Db;

use OCP\IDBConnection;
use OCA\Deck\Db\DeckMapper;

class ProjectMapper extends DeckMapper {

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

        $searchTerm = strtolower($name);
        $qb->select('*')
            ->from(self::TABLE_NAME)
            ->where('LOWER(name) LIKE ' . $qb->createNamedParameter('%' . $searchTerm . '%'));
        
        return $this->findEntities($qb);
    }

    public function findByUserId(string $userId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('p.*')
            ->from('custom_projects', 'p')
            ->innerJoin(
                'p',
                'circles_member',
                'm',
                $qb->expr()->eq('p.circle_id', 'm.circle_id')
            )
            ->where($qb->expr()->eq('m.user_id', $qb->createNamedParameter($userId)))
            ->orderBy('p.created_at', 'DESC');

        return $this->findEntities($qb);
    }
}