<?php

namespace OCA\ProjectCreatorAIO\Service;
use OCA\ProjectCreatorAIO\Db\ProjectMapper;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCA\Circles\CirclesManager;
use OCA\Circles\Service\FederatedUserService;
use OCA\Deck\Service\BoardService;
use OCP\Share\IManager as IShareManager;
use OCP\Share;
use OCP\Constants;
use OCP\IUserSession;
use OCP\Files\Folder;
use OCP\Files\Node;
use OCP\IUser;
use OCA\ProjectCreatorAIO\Db\Project;
use OCA\Deck\Db\Board;
use OCA\Circles\Model\Circle;
use OCA\Circles\Model\Member;
use Throwable;
use Exception;
 
class ProjectService {
    public function __construct(
        protected IUserSession $userSession,
        protected CirclesManager $circlesManager,
        protected IShareManager $shareManager,
        protected BoardService $boardService,
        protected IRootFolder $rootFolder,
        protected FederatedUserService $federatedUserService,
        protected ProjectMapper $projectMapper,
        protected FileTreeService $fileTreeService,
    ) {}

    /**
     * The main public method to create a complete project.
     * It orchestrates all the necessary steps and handles rollbacks.
     */
    public function createProject(
        string $name,
        string $number,
        int    $type,
        array  $members,
        string $address,
        string $description
    ): Project {
        $createdCircle = null;
        $createdBoard  = null;
        $createdFolders = [];

        try {
            $currentUser = $this->userSession->getUser();
            
            $createdCircle = $this->createCircleForProject(
                $name, 
                $members, 
                $currentUser
            );

            $createdBoard = $this->createBoardForProject(
                $name, 
                $currentUser, 
                $createdCircle->getSingleId()
            );

            $createdFolders = $this->createFoldersForProject(
                $name, 
                $members, 
                $currentUser, 
                $createdCircle->getSingleId()
            );
            
            $project = $this->projectMapper->createProject(
                $name, 
                $number, 
                $type, 
                $address, 
                $description,
                $currentUser->getUID(),
                $createdCircle->getSingleId(),
                $createdBoard->getId(),
                $createdFolders['shared']->getId(),
                $createdFolders['shared']->getPath()
            );

            return $project;

        } catch (Throwable $e) {

            $this->clearProjectResidus(
                $createdBoard, 
                $createdCircle, 
                $createdFolders['all']
            );

            throw new Exception(
                'Failed to create project: ' . $e->getMessage(), 500, $e
            );
        }
    }

    /**
     * Creates and populates a circle for the project.
     */
    private function createCircleForProject(string $projectName, array $members, IUser $owner): Circle {
        $this->circlesManager->startSession();

        $circleMembers = array_filter(
            $members, 
            fn($memberId) => $memberId !== $owner->getUID()
        );

        $circle = $this->circlesManager->createCircle("{$projectName} - Team", null, false, false);
        
        foreach ($circleMembers as $memberId) {
            $federatedUser = $this->federatedUserService->getLocalFederatedUser($memberId, true, true);
            $this->circlesManager->addMember($circle->getSingleId(), $federatedUser);
        }
        
        return $circle;
    }

    /**
     * Creates and shares a Deck board for the project.
     */
    private function createBoardForProject(string $projectName, IUser $owner, string $circleId): Board {
        $color = strtoupper(sprintf('%06X', random_int(0, 0xFFFFFF)));
        $board = $this->boardService->create("{$projectName} - Main Board", $owner->getUID(), $color);

        $this->boardService->addAcl(
            $board->getId(), 
            Share::SHARE_TYPE_CIRCLE, 
            $circleId, 
            true, 
            false, 
            false
        );

        return $board;
    }

    /**
     * Creates and shares all necessary folders for the project.
     * @return array{'shared': Folder, 'private': Folder[], 'all': Folder[]}
     */
    private function createFoldersForProject(
        string $projectName, 
        array $members, IUser 
        $owner, string 
        $circleId
    ): array {
        $userFolder = $this->rootFolder->getUserFolder($owner->getUID());
        $allCreatedFolders = [];

        // Create shared folders 
        $sharedFolderName = $this->getUniqueFolderName(
            $userFolder, 
            $projectName, 'Shared Files'
        );
        
        $sharedFolder = $userFolder->newFolder($sharedFolderName);
        $allCreatedFolders[] = $sharedFolder;
        $this->shareFolderWithCircle($sharedFolder, $circleId, $owner->getUID());

        // Create private folders for each member
        $privateFolders = [];
        foreach ($members as $memberId) {
            $suffix = "Private ({$memberId})";
            $privateFolderName = $this->getUniqueFolderName(
                $userFolder, 
                $projectName, 
                $suffix
            );

            $privateFolder = $userFolder->newFolder($privateFolderName);

            $allCreatedFolders[] = $privateFolder;
            $privateFolders[] = $privateFolder;

            $this->shareFolderWithUser($privateFolder, $memberId, $owner->getUID());
        }

        return ['shared' => $sharedFolder, 'private' => $privateFolders, 'all' => $allCreatedFolders];
    }

    private function shareFolderWithCircle(Node $folder, string $circleId, string $userId): void {
        $share = $this->shareManager->newShare();
        $share->setNode($folder);
        $share->setShareType(Share::SHARE_TYPE_CIRCLE);
        $share->setSharedWith($circleId);
        $share->setPermissions(
            Constants::PERMISSION_READ   | 
            Constants::PERMISSION_CREATE | 
            Constants::PERMISSION_UPDATE | 
            Constants::PERMISSION_SHARE
        );

        $share->setSharedBy($userId);
        $this->shareManager->createShare($share);
    }

    private function shareFolderWithUser(Node $folder, string $userId, string $ownerId): void {
        $share = $this->shareManager->newShare();
        $share->setNode($folder);
        $share->setShareType(Share::SHARE_TYPE_USER);
        $share->setSharedWith($userId);
        $share->setPermissions(
            Constants::PERMISSION_READ   | 
            Constants::PERMISSION_CREATE | 
            Constants::PERMISSION_UPDATE | 
            Constants::PERMISSION_DELETE | 
            Constants::PERMISSION_SHARE
        );

        $share->setSharedBy($ownerId);
        $this->shareManager->createShare($share);
    }

    private function getUniqueFolderName(Folder $baseFolder, string $projectName, string $suffix): string {
        $folderName = "{$projectName} - {$suffix}";
        if (!$baseFolder->nodeExists($folderName)) {
            return $folderName;
        }
        $counter = 2;
        while (true) {
            $folderName = "{$projectName} ({$counter}) - {$suffix}";
            if (!$baseFolder->nodeExists($folderName)) {
                return $folderName;
            }
            $counter++;
        }
    }

    private function clearProjectResidus(
        ?Board $createdBoard, 
        ?Circle $createdCircle, 
        ?array $createdFolders
    ): void {
        $currentUser = $this->userSession->getUser();

        if (!empty($createdFolders)) {
            foreach ($createdFolders as $folder) {
                if ($folder !== null && $folder->isDeletable()) {
                    $folder->delete();
                }
            }
        }

        if ($createdBoard !== null) {
            $this->boardService->delete($createdBoard->getId());
        }
        
        if ($createdCircle !== null) {
            $federatedUser = $this->circlesManager->getFederatedUser(
                $currentUser->getUID(),
                Member::TYPE_USER
            );
            
            $this->circlesManager->startSession($federatedUser);
            $this->circlesManager->destroyCircle($createdCircle->getSingleId());
        }
    }

    /**
     * Finds the project folder and delegates tree-building to the FileTreeService.
     */
    public function getProjectFiles(int $projectId): array {
        $project = $this->projectMapper->find($projectId);
        if ($project === null) {
            throw new Exception("Project with ID $projectId not found.");
        }

        try {
            $projectFolders = $this->rootFolder->getById($project->getFolderId());
            if (empty($projectFolders)) {
                throw new NotFoundException("Project folder node not found on the filesystem.");
            }

            $projectFolder = $projectFolders[0];

            $files = $this->fileTreeService->buildTreeFromNode($projectFolder);
            
            return ['files' => $files];

        } catch (NotFoundException $e) {
            throw new Exception("Project folder is not found or has been deleted.");
        }
    }
    
    public function findProjectByBoard(int $boardId) {
        return $this->projectMapper->findByBoardId($boardId);
    }
}