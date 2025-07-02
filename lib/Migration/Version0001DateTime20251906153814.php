<?php

namespace OCA\ProjectCreatorAio\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version0001DateTime20251906153814 extends SimpleMigrationStep {

    public const TABLE_NAME = 'custom_projects';
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ISchemaWrapper {
        $schema = $schemaClosure();

        if (!$schema->hasTable(self::TABLE_NAME)) {
            $table = $schema->createTable(self::TABLE_NAME);
            
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true
            ]);
            $table->addColumn('name', 'string', [
                'length' => 255,
                'notnull' => true,
            ]);
            $table->addColumn('number', 'string', [
                'length' => 255,
                'notnull' => true,
            ]);
            $table->addColumn('type', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('address', 'string', [
                'notnull' => true,
            ]);
            $table->addColumn('description', 'text', [
                'notnull' => true,
            ]);
            $table->addColumn('owner_id', 'string', [
                'notnull' => true,
            ]);
            $table->addColumn('circle_id', 'string', [
                'notnull' => true,
            ]);
            $table->addColumn('board_id', 'string', [
                'notnull' => true,
            ]);
            $table->addColumn('folder_id', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('folder_path', 'string', [
                'notnull' => true,
            ]);
            $table->addColumn('status', 'integer', [
                'notnull' => true,
                'default' => 1
            ]);
            $table->addColumn('created_at', 'datetime', ['notnull' => true]);
            $table->addColumn('updated_at','datetime', ['notnull' => true]);

            $table->setPrimaryKey(['id']);
            $table->addOption('mysql_engine', 'InnoDB');
            $table->addIndex(['name'],'projectNameIndex', ['fulltext']);
            $table->addIndex(['owner_id'],'projectOwnerIdIndex', []);
            $table->addUniqueIndex(['circle_id'], 'projectCircleIdUnique');
        } else {
            $table = $schema->getTable(self::TABLE_NAME);
            
            if($table->hasColumn('folder_name')) {
                $table->dropColumn('folder_name');
            }

            if(!$table->hasColumn('status')) {
                $table->addColumn('status', 'integer', [
                    'notnull' => true,
                    'default' => 1
                ]);
            }
            
            if(!$table->hasColumn('folder_path')) {
                $table->addColumn('folder_path', 'string', [
                    'notnull' => true
                ]);
            }

            if(!$table->hasColumn('folder_id')) {
                $table->addColumn('folder_id', 'integer', [
                    'notnull' => true
                ]);
            }
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