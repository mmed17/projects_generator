<?php
namespace OCA\ProjectCreatorAIO\Service;

use OCP\Files\Folder;
use OCP\Files\Node;

class FileTreeService {
    /**
     * Takes a starting folder and recursively builds an array of its contents.
     */
    public function buildTreeFromNode(Folder $folder): array {
        $children = $folder->getDirectoryListing();
        return array_map([$this, 'transformNodeToArray'], $children);
    }

    /**
     * The recursive function that converts a Node into a structured array.
     */
    private function transformNodeToArray(Node $node): array {
        $info = [
            'id'       => $node->getId(),
            'name'     => $node->getName(),
            'type'     => ($node instanceof Folder) ? 'folder' : 'file',
            'mimetype' => $node->getMimeType(),
            'size'     => $node->getSize(),
            'path'     => $node->getPath(),
        ];
        
        if ($node instanceof Folder) {
            $info['children'] = $this->buildTreeFromNode($node);
        }

        return $info;
    }
}