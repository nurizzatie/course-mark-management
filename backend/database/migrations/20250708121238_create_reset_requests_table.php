<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateResetRequestsTable extends AbstractMigration
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
    $this->table('reset_requests')
        ->addColumn('user_id', 'integer')
        ->addColumn('status', 'enum', ['values' => ['pending', 'approved', 'rejected'], 'default' => 'pending'])
        ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
        ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE'])
        ->create();
}

}
