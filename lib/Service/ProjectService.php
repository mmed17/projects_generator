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
        protected FileTreeService $fileTreeService
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
            $owner = $this->userSession->getUser();
            
            $createdCircle = $this->createCircleForProject(
                $name, 
                $members, 
                $owner
            );

            $createdBoard = $this->createBoardForProject(
                $name, 
                $owner, 
                $createdCircle->getSingleId()
            );

            $createdFolders = $this->createFoldersForProject(
                $name, 
                $members, 
                $owner, 
                $createdCircle->getSingleId()
            );

            $project = $this->projectMapper->createProject(
                $name, 
                $number, 
                $type, 
                $address, 
                $description,
                $owner->getUID(),
                $createdCircle->getSingleId(),
                $createdBoard->getId(),
                $createdFolders['shared']->getId(),
                $createdFolders['shared']->getPath(),
                $createdFolders['private']
            );

            return $project;

        } catch (Throwable $e) {

            $this->cleanupResources(
                $createdBoard, 
                $createdCircle, 
                $createdFolders['all']
            );

            throw new Exception($e, 500);
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
        string $projectName, array $members, IUser $owner, string $circleId
    ): array {
        $ownerFolder = $this->rootFolder->getUserFolder($owner->getUID());
        $allCreatedFolders = [];
        
        // Create shared folders 
        $sharedFolderName = $this->getUniqueFolderName(
            $projectName, 
            'Shared Files', 
            $ownerFolder
        );
        
        $sharedFolder = $ownerFolder->newFolder($sharedFolderName);
        $allCreatedFolders[] = $sharedFolder;

        $this->shareFolderWithCircle(
            $sharedFolder, 
            $circleId, 
            $owner->getUID()
        );

        // Create private folders for each member
        $privateFolders = [];
        foreach ($members as $memberId) {

            $privateFolderName = $this->getUniqueFolderName(
                $projectName, 
                "Private Files ($memberId)" ,
                $ownerFolder
            );

            $privateFolder = $ownerFolder->newFolder($privateFolderName);
            
            $allCreatedFolders[] = $privateFolder;
            $privateFolders[] = [
                'userId' => $memberId,
                'folderId' => $privateFolder->getId(),
                'path' => $privateFolder->getPath(),
            ];

            $this->shareFolderWithUser($privateFolder, $memberId, $owner->getUID());
        }

        return [
            'shared' => $sharedFolder, 
            'private' => $privateFolders, 
            'all' => $allCreatedFolders
        ];
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

    private function getUniqueFolderName(string $projectName, string $suffix, Folder $folder): string {
        $folderName = "{$projectName} - {$suffix}";

        if (!$folder->nodeExists($folderName)) {
            return $folderName;
        }

        $counter = 2;
        while (true) {
            $folderName = "{$projectName} ({$counter}) - {$suffix}";
            if (!$folder->nodeExists($folderName)) {
                return $folderName;
            }
            $counter++;
        }
    }

    private function cleanupResources(
        ?Board  $board, 
        ?Circle $circle, 
        ?array  $folders
    ): void {
        $user = $this->userSession->getUser();

        if (!empty($folders)) {
            foreach ($folders as $folder) {
                if ($folder !== null && $folder->isDeletable()) {
                    $folder->delete();
                }
            }
        }

        if ($board !== null) {
            $this->boardService->delete($board->getId());
        }
        
        if ($circle !== null) {
            $federatedUser = $this->circlesManager->getFederatedUser(
                $user->getUID(),
                Member::TYPE_USER
            );
            
            $this->circlesManager->startSession($federatedUser);
            $this->circlesManager->destroyCircle($circle->getSingleId());
        }
    }

    /**
     * Finds the project folder and delegates tree-building to the FileTreeService.
     */
    public function getProjectFiles(int $projectId): array {
        $currentUser = $this->userSession->getUser();

        $project = $this->projectMapper->find($projectId);
        if ($project === null) {
            throw new Exception("Project with ID $projectId not found.");
        }

        try {
            $ownerFolder = $this->rootFolder->getUserFolder($project->getOwnerId());
            $sharedFiles  = $ownerFolder->getById($project->getFolderId());

            if (empty($sharedFiles)) {
                throw new NotFoundException("Project folder node not found on the filesystem.");
            }

            $sharedFilesTree = $this->fileTreeService->buildTree($sharedFiles[0]);
            
            $privateFolderLinks = [];
            if ($project->getOwnerId() === $currentUser->getUID()) {
                $privateFolderLinks = $this->projectMapper->findAllPrivateFoldersByProject($projectId);
            } else {
                $link = $this->projectMapper->findPrivateFolderForUser(
                    $projectId, 
                    $currentUser->getUID()
                );
                if ($link !== null) {
                    $privateFolderLinks[] = $link;
                }
            }

            $privateFilesTrees = [];
            foreach ($privateFolderLinks as $link) {
                try {
                    $privateFolderNode = $ownerFolder->getById($link->getFolderId())[0];
                    $privateFilesTrees[] = $this->fileTreeService->buildTree($privateFolderNode);
                } catch (NotFoundException $e) {
                    continue;
                }
            }

            return [
                'shared' => [$sharedFilesTree], 
                'private' => $privateFilesTrees
            ];
            
        } catch (NotFoundException $e) {
            throw new Exception("Project folder is not found or has been deleted.");
        }
    }
    
    public function findProjectByBoard(int $boardId) {
        return $this->projectMapper->findByBoardId($boardId);
    }

     /**
     * @return PrivateFolderLink[]
     */
    public function findAllPrivateFoldersByProject(int $projectId): array {
        return $this->linkMapper->findByProject($projectId);
    }
}