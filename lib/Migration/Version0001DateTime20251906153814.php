<?php

namespace OCA\ProjectCreatorAio\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version0001DateTime20251906153814 extends SimpleMigrationStep {

    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {}

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ISchemaWrapper {
        $schema = $schemaClosure();

        if (!$schema->hasTable('custom_projects')) {
            $table = $schema->createTable('custom_projects');
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
            $table->addColumn('folder_name', 'string', [
                'notnull' => true,
            ]);

            $table->addColumn('created_at', 'datetime', ['notnull' => true]);
            $table->addColumn('updated_at','datetime', ['notnull' => true]);

            $table->setPrimaryKey(['id']);
            $table->addOption('mysql_engine', 'InnoDB');
            $table->addIndex(['name'],'projectNameIndex', ['fulltext']);
            $table->addIndex(['owner_id'],'projectOwnerIdIndex', []);
        }
        
        return $schema;
    }

    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {}
}
