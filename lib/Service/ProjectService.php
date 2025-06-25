<?php


namespace OCA\ProjectCreatorAIO\Service;
use OCA\ProjectCreatorAIO\Db\ProjectMapper;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;
use OCP\Files\Node;

class ProjectService {
    private IRootFolder $rootFolder;
    private ProjectMapper $projectMapper;

    public function __construct(IRootFolder $rootFolder, ProjectMapper $projectMapper) {
        $this->rootFolder = $rootFolder;
        $this->projectMapper = $projectMapper;
    }

    /**
     * Finds the project for a given Circle ID and builds a file tree.
     */
    public function getTreeForProjectTeam(string $circleId): ?array {
        $project = $this->projectMapper->findByCircleId($circleId);
        if ($project === null) {
            return null;
        }

        try {
            $userFolder = $this->rootFolder->getUserFolder($project->getOwnerId());
            if (!$userFolder->nodeExists($project->getFolderName())) {
                return null;
            }

            $node = $userFolder->get($project->getFolderName());
            if ($node instanceof Folder) {
                return $this->buildNodeInfo($node);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

     /**
     * The recursive function that builds the nested array structure.
     */
    private function buildNodeInfo(Node $node): array {
        $info = [
            'id' => $node->getId(),
            'name' => $node->getName(),
            'type' => ($node instanceof Folder) ? 'folder' : 'file',
            'mimetype' => $node->getMimeType(),
            'size' => $node->getSize(),
            'path' => $node->getPath(),
        ];
        
        if ($node instanceof Folder) {
            $children = $node->getDirectoryListing();
            $info['children'] = array_map([$this, 'buildNodeInfo'], $children);
            $info['isEmpty'] = empty($children);
        }

        return $info;
    }
}