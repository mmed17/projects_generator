<?php


namespace OCA\ProjectCreatorAIO\Service;
use Exception;
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
    public function getProjectFolderContents(int $projectId) {
        $project = $this->projectMapper->find($projectId);
        if ($project === null) {
            throw new Exception("Project Not found.");
        }

        $userFolder = $this->rootFolder->getUserFolder($project->getOwnerId());
        $projectFolders = $this->rootFolder->getById($project->getFolderId());
        
        if(count($projectFolders) === 0) {
            throw new Exception("Project folder is not found.");
        }

        $projectFolder = $projectFolders[0];
        if (!$userFolder->nodeExists($projectFolder->getName())) {
            throw new Exception("Project folder is not found.");
        }
        
        $node = $userFolder->get($projectFolder->getName());
        if ($node instanceof Folder) {
            $folder = $this->buildNodeInfo($node);
            return [
                'folder' => $folder
            ];
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