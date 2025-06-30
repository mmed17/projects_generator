<?php
namespace OCA\Projectcreatoraio\Controller;

use OCA\ProjectCreatorAIO\Service\ProjectService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\AppFramework\NoCSRFRequired;
use OCP\IUserSession;
use OCA\Circles\CirclesManager;
use OCA\Circles\Service\FederatedUserService;
use OCA\Deck\Service\BoardService;
use OCP\Files\IRootFolder;
use OCP\Share\IManager as IShareManager;
use OCP\Files\Node;
use OCP\Share;
use OCP\Constants;
use OCA\ProjectCreatorAIO\Db\ProjectMapper;
use OCA\Circles\Model\Member;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\AppFramework\Http\OCS\OCSForbiddenException; 

class ProjectApiController extends Controller {
    public function __construct(
        string $appName,
        IRequest $request,
        protected IUserSession $userSession,
        protected CirclesManager $circlesManager,
        protected IShareManager $shareManager,
        protected BoardService $boardService,
        protected IRootFolder $rootFolder,
        protected FederatedUserService $federatedUserService,
        protected ProjectMapper $projectMapper,
        protected ProjectService $projectService,
    ) {
        parent::__construct($appName, $request);
        $this->request = $request;
        $this->circlesManager->startSession();
    }
    
    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     *  @return bool
     */
    public function get(int $projectId) {
        $project = $this->projectMapper->find($projectId);
        return $project;
    }

    public function randomColor(): string {
        $randomInt = random_int(0, 0xFFFFFF);
        return strtoupper(sprintf('%06X', $randomInt));
    }

    public function shareFolderWithCircle(Node $folder, string $circleId, string $userId): void {
        try {
            $share = $this->shareManager->newShare();
            $share->setNode($folder);
            $share->setShareType(Share::SHARE_TYPE_CIRCLE);
            $share->setSharedWith($circleId);
            $share->setPermissions(
                Constants::PERMISSION_READ | 
                Constants::PERMISSION_CREATE | 
                Constants::PERMISSION_UPDATE |
                Constants::PERMISSION_SHARE
            );
            $share->setSharedBy($userId);
            $this->shareManager->createShare($share);

        } catch (NotFoundException $e) {
            throw new \Exception("Folder not found");
        } catch (\Throwable $e) {
            throw new \Exception("Failed to share folder: ". $e->getMessage());
        }
    }

    /**
     * Create a new project
     * 
     * @return DataResponse
     * 
     * @NoCSRFRequired
     */
    public function create(
        string $name,
        string $number,
        int $type,
        array $members,
        string $address = '',
        string $description = '',
    ): DataResponse {

        $createdCircle = null;
        $createdBoard  = null;
        $createdFolder = null;

        try {
            $currentUser = $this->userSession->getUser();
            $timestamp = (new \DateTime())->format('YmdHis');

            // 1. Create circle and add members
            $circleMembersId = array_filter(
                $members, 
                fn($memberId) => $memberId!== $currentUser->getUID()
            );

            $createdCircle = $this->circlesManager->createCircle("{$name} - Team", null, false, false);
            
            foreach ($circleMembersId as $memberId) {
                $federatedUser = $this->federatedUserService->getLocalFederatedUser($memberId, true, true);
                $this->circlesManager->addMember($createdCircle->getSingleId(), $federatedUser);
            }

            // 2. Create board
            $createdBoard = $this->boardService->create("{$name} - Main Board", $currentUser->getUID(), $this->randomColor());

            // 3. Create folder
            $folderName = "{$name}:{$timestamp} - Main Files";
            $userFolder = $this->rootFolder->getUserFolder($currentUser->getUID());
            $createdFolder = $userFolder->newFolder($folderName);
            
            // 4. Share board with circle 
            $this->boardService->addAcl(
                $createdBoard->id,
                Share::SHARE_TYPE_CIRCLE,
                $createdCircle->getSingleId(),
                true,
                false,
                false
            );

            // 5. Share folder with circle
            $this->shareFolderWithCircle(
                $createdFolder, 
                $createdCircle->getSingleId(), 
                $currentUser->getUID()
            );

            // 6. Insert project into DB (if applicable)
            $project = $this->projectMapper->createProject(
                $name, 
                $number, 
                $type, 
                $address, 
                $description, 
                $currentUser->getUID(), 
                $createdCircle->getSingleId(), 
                $createdBoard->id,
                $createdFolder->getId(),
                $createdFolder->getPath()
            );
            
            return new DataResponse([
                'message' => 'Project created successfully',
                'projectId' => $project->id,
            ]);

        } catch (\Throwable $e) {
            if ($createdFolder!== null && $createdFolder->isDeletable()) {
                $createdFolder->delete();
            }

            if ($createdBoard!== null) {
                $this->boardService->delete($createdBoard->id);
            }

            if ($createdCircle !== null) {
                $federatedUser = $this->circlesManager->getFederatedUser($currentUser->getUID(), Member::TYPE_USER);
                $this->circlesManager->startSession($federatedUser);
                $this->circlesManager->destroyCircle($createdCircle->getSingleId());
            }
            
            return new DataResponse([
                'message' => 'Failed to create project: '. $e->getMessage()
            ], 500);
        }
    }

    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     *  @return DataResponse
     */
    public function list(): DataResponse {
        $currentUser = $this->userSession->getUser();
        $results = $this->projectMapper->findByUserId($currentUser->getUID());
        return new DataResponse($results);
    }

    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     *  @return DataResponse
     */
    public function getProjectFiles(int $projectId): DataResponse {
        $folder = $this->projectService->getProjectFiles($projectId);
        return new DataResponse($folder);
    }

    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     *  @return bool
     */
    public function updateProjectStatus(int $projectId, int $status): bool {
        $this->projectMapper->updateProjectStatus($projectId, $status);
        return true;
    }

    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     *  @return DataResponse
     */
    public function getProjectByCircleId(string $circleId): DataResponse {
        $project = $this->projectMapper->findByCircleId($circleId);
        return new DataResponse([
            'project' => $project
        ]);
    }

    /**
     * Get all projects for a specific user.
     *
     * @NoCSRFRequired
     * @AdminRequired
     *
     * @param string $userId The user ID to fetch projects for.
     * @return DataResponse
     * @throws OCSForbiddenException if the current user is not an admin
     * @throws OCSNotFoundException if the specified user does not exist
     */
    public function listByUser(string $userId): DataResponse {
        $projects = $this->projectMapper->findByUserId($userId);
        return new DataResponse($projects);
    }
}