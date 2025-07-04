<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
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
        $this->table('users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('name', 'string')
            ->addColumn('matric_number', 'string', ['limit' => 20])
            ->addColumn('email', 'string')
            ->addColumn('password', 'string')
            ->addColumn('role', 'enum', ['values' => ['student', 'lecturer', 'advisor', 'admin']])
            ->addTimestamps()
            ->addIndex(['matric_number'], ['unique' => true])
            ->create();
    }
}
