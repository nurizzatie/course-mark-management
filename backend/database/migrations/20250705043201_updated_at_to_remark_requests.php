<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdatedAtToRemarkRequests extends AbstractMigration
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

    
    if (!$table->hasColumn('updated_at')) {
        $table->addColumn('updated_at', 'timestamp', [
            'null' => true,
            'after' => 'created_at',
            'default' => null
        ]);
    }

    $table->update();
    }
}
