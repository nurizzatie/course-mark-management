<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAppealTrackingToRemarkRequests extends AbstractMigration
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
$table = $this->table('remark_requests');

        // Add appeal_count column
        $table->addColumn('appeal_count', 'integer', ['default' => 0, 'after' => 'status'])

              // Optionally add is_appeal (to know if it's currently an appeal attempt)
              ->addColumn('is_appeal', 'boolean', ['default' => false, 'after' => 'appeal_count'])

              ->update();
    }
}   
