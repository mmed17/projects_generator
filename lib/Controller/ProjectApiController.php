<?php
namespace OCA\Projectcreatoraio\Controller;

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
use OCA\ProjectCreatorAIO\Db\Project;

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
    ) {
        parent::__construct($appName, $request);
        $this->request = $request;
        $this->federatedUserService->initCurrentUser();
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
        $currentUser = $this->userSession->getUser();
        $timestamp = (new \DateTime())->format('YmdHis');

        $createdCircle = null;
        $createdBoard  = null;
        $createdFolder = null;

        try {
            // 1. Create circle and add members
            $circleMembersId = array_filter($members, fn($memberId) => $memberId !== $currentUser->getUID());
            $createdCircle = $this->circlesManager->createCircle("{$name} - Team", null, true, true);
            
            $federatedUsers = [];
            foreach ($circleMembersId as $memberId) {
                $federatedUser = $this->federatedUserService->getLocalFederatedUser($memberId, true, true);
                $this->circlesManager->addMember($createdCircle->getSingleId(), $federatedUser);
                $federatedUsers[] = $federatedUser;
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
                false, false, false
            );

            // 5. Share folder with circle
            $this->shareFolderWithCircle($createdFolder, $createdCircle->getSingleId(), $currentUser->getUID());

            // 6. Insert project into DB (if applicable)
            $projectId = $this->projectMapper->createProject(
                $name, 
                $number, 
                $type, 
                $address, 
                $description, 
                $currentUser->getUID(), 
                $createdCircle->getSingleId(), 
                $createdBoard->id, 
                $folderName,
            );
            
            return new DataResponse([
                'message' => 'Project created successfully',
                'project' => $projectId,
            ]);

        } catch (\Throwable $e) {
            if ($createdFolder !== null && $createdFolder->isDeletable()) {
                $createdFolder->delete();
            }

            if ($createdBoard !== null) {
                $this->boardService->delete($createdBoard->id);
            }

            // if ($createdCircle !== null) {
            //     $this->circlesManager->destroyCircle($createdCircle->getSingleId());
            // }

            return new DataResponse([
                'message' => 'Failed to create project: ' . $e->getMessage()
            ], 500);
        }
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
            $share->setPermissions(Constants::PERMISSION_READ);
            $share->setSharedBy($userId);
            $share->setShareOwner($userId);

            $this->shareManager->createShare($share);

        } catch (NotFoundException $e) {
            throw new \Exception("Folder not found");
        } catch (\Throwable $e) {
            throw new \Exception("Failed to share folder: " . $e->getMessage());
        }
    }
}