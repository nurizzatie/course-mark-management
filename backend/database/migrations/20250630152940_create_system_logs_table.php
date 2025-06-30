<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSystemLogsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $this->table('system_logs', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('action_by', 'integer', ['signed' => false])
            ->addColumn('action_type', 'string', ['limit' => 50])
            ->addColumn('description', 'text')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('action_by', 'users', 'id')
            ->create();
    }
}
