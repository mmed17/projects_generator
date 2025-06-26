<?php

namespace OCA\ProjectCreatorAio\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;
use OCP\DB\QueryBuilder\IQueryBuilder;

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
            $table = $schema->getTable('custom_projects');
            $table->addColumn('status', 'integer', [
                'notnull' => true,
                'default' => 1
            ]);
        }
        
        return $schema;
    }

    /**
     * This function runs after the schema has been changed.
     * It's the perfect place to update data in existing rows.
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
        // Get the database connection from the server container
        $db = \OC::$server->getDatabaseConnection();

        // Get a query builder instance
        $qb = $db->getQueryBuilder();

        // Build an UPDATE query to set the status for any old rows
        // that might not have the default value applied.
        $qb->update('custom_projects')
            ->set('status', $qb->createNamedParameter(1, IQueryBuilder::PARAM_INT))
            // This condition is a safeguard. While the `default` in the schema change
            // should handle most cases, this ensures any rows that somehow ended up
            // with a NULL status are also updated.
            ->where($qb->expr()->isNull('status'));

        // Execute the query
        $qb->executeStatement();

        // It's good practice to log what the migration step did.
        $output->info('Set default status for existing projects.');
    }
}
