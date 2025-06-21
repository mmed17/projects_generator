<?php

namespace OCA\ProjectCreatorAIO\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

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

    public function list(string $ownerId) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from(self::TABLE_NAME)
           ->where($qb->expr()->eq('owner_id', $qb->createNamedParameter($ownerId)))
           ->orderBy('created_at', 'DESC');
        
        return $this->findEntities($qb);
    }

    public function getUserProjects(string $userId): array {
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

    /**
     * Implements the query for "undone tasks per user".
     * @param string $userId The participant to filter by (e.g., 'admin')
     * @return array
     */
    public function findUndoneTasksByUser(string $userId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('cards.*', 'stacks.board_id')
            ->from('deck_cards', 'cards')
            ->innerJoin(
                'cards',
                'deck_assigned_users',
                'assignments',
                $qb->expr()->eq('cards.id', 'assignments.card_id')
            )
            ->innerJoin('cards', 'deck_stacks', 'stacks', $qb->expr()->eq('cards.stack_id', 'stacks.id'))
            ->where(
                $qb->expr()->eq('assignments.participant', $qb->createNamedParameter($userId))
            )
            ->andWhere(
                $qb->expr()->isNull('cards.done')
            )
            ->orderBy('cards.created_at', 'DESC');

        return $qb->execute()->fetchAll();
    }

    /**
     * Implements the query for "undone tasks per project".
     * @param int $projectId The ID of the project
     * @return array
     */
    public function findUndoneTasksByProject(int $projectId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('assignments.participant', 'cards.*', 'projects.board_id')
            ->from(self::TABLE_NAME, 'projects')
            ->innerJoin(
                'projects',
                'deck_stacks',
                'stacks',
                $qb->expr()->eq('stacks.board_id', 'projects.board_id')
            )
            ->innerJoin(
                'stacks',
                'deck_cards',
                'cards',
                $qb->expr()->eq('cards.stack_id', 'stacks.id')
            )
            ->innerJoin(
                'cards',
                'deck_assigned_users',
                'assignments',
                $qb->expr()->eq('cards.id', 'assignments.card_id')
            )
            ->where(
                $qb->expr()->eq('projects.id', $qb->createNamedParameter($projectId, \PDO::PARAM_INT))
            )
            ->andWhere(
                $qb->expr()->isNull('cards.done')
            )
            ->orderBy('cards.created_at', 'DESC');

        return $qb->execute()->fetchAll();
    }

    /**
     * Implements the query for "undone tasks per user in a project".
     * @param int $projectId The ID of the project
     * @param string $userId The participant to filter by
     * @return array
     */
    public function findUndoneTasksByUserInProject(int $projectId, string $userId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('cards.*', 'projects.board_id')
            ->from(self::TABLE_NAME, 'projects')
            ->innerJoin(
                'projects',
                'deck_stacks',
                'stacks',
                $qb->expr()->eq('stacks.board_id', 'projects.board_id')
            )
            ->innerJoin(
                'stacks',
                'deck_cards',
                'cards',
                $qb->expr()->eq('cards.stack_id', 'stacks.id')
            )
            ->innerJoin(
                'cards',
                'deck_assigned_users',
                'assignments',
                $qb->expr()->eq('cards.id', 'assignments.card_id')
            )
            ->where(
                $qb->expr()->eq('projects.id', $qb->createNamedParameter($projectId, \PDO::PARAM_INT))
            )
            ->andWhere(
                $qb->expr()->eq('assignments.participant', $qb->createNamedParameter($userId))
            )
            ->andWhere(
                $qb->expr()->isNull('cards.done')
            )
            ->orderBy('cards.created_at', 'DESC');

        return $qb->execute()->fetchAll();
    }

    /**
     * Implements the query for "overdue tasks per user".
     * @param string $userId The participant to filter by
     * @return array
     */
    public function findOverdueTasksByUser(string $userId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('cards.*', 'stacks.board_id')
            ->from('deck_cards', 'cards')
            ->innerJoin(
                'cards',
                'deck_assigned_users',
                'assignments',
                $qb->expr()->eq('cards.id', 'assignments.card_id')
            )
            ->innerJoin('cards', 'deck_stacks', 'stacks', $qb->expr()->eq('cards.stack_id', 'stacks.id'))
            ->where(
                $qb->expr()->eq('assignments.participant', $qb->createNamedParameter($userId))
            )
            ->andWhere(
                $qb->expr()->isNull('cards.done')
            )
            ->andWhere('cards.duedate <= CURRENT_TIMESTAMP()')
            ->orderBy('cards.duedate', 'DESC');

        return $qb->execute()->fetchAll();
    }

    /**
     * Implements the query for "overdue tasks per project".
     * @param int $projectId The ID of the project
     * @return array
     */
    public function findOverdueTasksByProject(int $projectId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('assignments.participant', 'cards.*', 'projects.board_id')
            ->from(self::TABLE_NAME, 'projects')
            ->innerJoin(
                'projects',
                'deck_stacks',
                'stacks',
                $qb->expr()->eq('stacks.board_id', 'projects.board_id')
            )
            ->innerJoin(
                'stacks',
                'deck_cards',
                'cards',
                $qb->expr()->eq('cards.stack_id', 'stacks.id')
            )
            ->innerJoin(
                'cards',
                'deck_assigned_users',
                'assignments',
                $qb->expr()->eq('cards.id', 'assignments.card_id')
            )
            ->where(
                $qb->expr()->eq('projects.id', $qb->createNamedParameter($projectId, \PDO::PARAM_INT))
            )
            ->andWhere(
                $qb->expr()->isNull('cards.done')
            )
            ->andWhere('cards.duedate <= CURRENT_TIMESTAMP()')
            ->orderBy('cards.created_at', 'DESC');

        return $qb->execute()->fetchAll();
    }

    /**
     * Implements the query for "overdue tasks per user per project".
     * @param int $projectId The ID of the project
     * @param string $userId The participant to filter by
     * @return array
     */
    public function findOverdueTasksByUserInProject(int $projectId, string $userId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('cards.*', 'projects.board_id')
            ->from(self::TABLE_NAME, 'projects')
            ->innerJoin(
                'projects',
                'deck_stacks',
                'stacks',
                $qb->expr()->eq('stacks.board_id', 'projects.board_id')
            )
            ->innerJoin(
                'stacks',
                'deck_cards',
                'cards',
                $qb->expr()->eq('cards.stack_id', 'stacks.id')
            )
            ->innerJoin(
                'cards',
                'deck_assigned_users',
                'assignments',
                $qb->expr()->eq('cards.id', 'assignments.card_id')
            )
            ->where(
                $qb->expr()->eq('projects.id', $qb->createNamedParameter($projectId, \PDO::PARAM_INT))
            )
            ->andWhere(
                $qb->expr()->eq('assignments.participant', $qb->createNamedParameter($userId))
            )
            ->andWhere(
                $qb->expr()->isNull('cards.done')
            )
            ->andWhere('cards.duedate <= CURRENT_TIMESTAMP()')
            ->orderBy('cards.created_at', 'DESC');

        return $qb->execute()->fetchAll();
    }

    /**
     * Implements the query for "upcoming tasks per user".
     * @param string $userId The participant to filter by
     * @return array
     */
    public function findUpcomingTasksByUser(string $userId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('cards.*', 'stacks.board_id')
            ->from('deck_cards', 'cards')
            ->innerJoin(
                'cards',
                'deck_assigned_users',
                'assignments',
                $qb->expr()->eq('cards.id', 'assignments.card_id')
            )
            ->innerJoin('cards', 'deck_stacks', 'stacks', $qb->expr()->eq('cards.stack_id', 'stacks.id'))
            ->where(
                $qb->expr()->eq('assignments.participant', $qb->createNamedParameter($userId))
            )
            ->andWhere(
                $qb->expr()->isNull('cards.done')
            )
            ->andWhere('cards.duedate > CURRENT_TIMESTAMP()')
            ->orderBy('cards.duedate', 'DESC');

        return $qb->execute()->fetchAll();
    }

    /**
     * Implements the query for "upcoming tasks per project".
     * @param int $projectId The ID of the project
     * @return array
     */
    public function findUpcomingTasksByProject(int $projectId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('assignments.participant', 'cards.*', 'projects.board_id')
            ->from(self::TABLE_NAME, 'projects')
            ->innerJoin(
                'projects',
                'deck_stacks',
                'stacks',
                $qb->expr()->eq('stacks.board_id', 'projects.board_id')
            )
            ->innerJoin(
                'stacks',
                'deck_cards',
                'cards',
                $qb->expr()->eq('cards.stack_id', 'stacks.id')
            )
            ->innerJoin(
                'cards',
                'deck_assigned_users',
                'assignments',
                $qb->expr()->eq('cards.id', 'assignments.card_id')
            )
            ->where(
                $qb->expr()->eq('projects.id', $qb->createNamedParameter($projectId, \PDO::PARAM_INT))
            )
            ->andWhere(
                $qb->expr()->isNull('cards.done')
            )
            ->andWhere('cards.duedate > CURRENT_TIMESTAMP()')
            ->orderBy('cards.created_at', 'DESC');

        return $qb->execute()->fetchAll();
    }

    /**
     * Implements the query for "upcoming tasks per user per project".
     * @param int $projectId The ID of the project
     * @param string $userId The participant to filter by
     * @return array
     */
    public function findUpcomingTasksByUserInProject(int $projectId, string $userId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('cards.*', 'projects.board_id')
            ->from(self::TABLE_NAME, 'projects')
            ->innerJoin(
                'projects',
                'deck_stacks',
                'stacks',
                $qb->expr()->eq('stacks.board_id', 'projects.board_id')
            )
            ->innerJoin(
                'stacks',
                'deck_cards',
                'cards',
                $qb->expr()->eq('cards.stack_id', 'stacks.id')
            )
            ->innerJoin(
                'cards',
                'deck_assigned_users',
                'assignments',
                $qb->expr()->eq('cards.id', 'assignments.card_id')
            )
            ->where(
                $qb->expr()->eq('projects.id', $qb->createNamedParameter($projectId, \PDO::PARAM_INT))
            )
            ->andWhere(
                $qb->expr()->eq('assignments.participant', $qb->createNamedParameter($userId))
            )
            ->andWhere(
                $qb->expr()->isNull('cards.done')
            )
            ->andWhere('cards.duedate > CURRENT_TIMESTAMP()')
            ->orderBy('cards.created_at', 'DESC');

        return $qb->execute()->fetchAll();
    }
}