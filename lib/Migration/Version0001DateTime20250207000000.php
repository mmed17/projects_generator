<?php
namespace OCA\ProjectCreatorAIO\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version0001DateTime20250207000000 extends SimpleMigrationStep {

    public const TABLE_NAME = 'proj_private_folders';
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ISchemaWrapper {
        $schema = $schemaClosure();

        if (!$schema->hasTable(self::TABLE_NAME)) {
            $table = $schema->createTable(self::TABLE_NAME);
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true
            ]);
            $table->addColumn('project_id', 'integer', [
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('user_id', 'string', [
                'length' => 64,
                'notnull' => true,
            ]);
            $table->addColumn('folder_id', 'integer', [
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('folder_path', 'string', [
                'notnull' => true,
                'unsigned' => true,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['project_id'], 'folders_project_idx');
            $table->addUniqueIndex(['project_id', 'user_id'], 'folders_proj_user_idx');
        }

        return $schema;
    }

    /**
     * This function runs after the schema has been changed.
     * It's the perfect place to update data in existing rows.
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {}
    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {}
}