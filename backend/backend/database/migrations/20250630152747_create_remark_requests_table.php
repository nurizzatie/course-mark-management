<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRemarkRequestsTable extends AbstractMigration
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
        $this->table('remark_requests', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('student_id', 'integer', ['signed' => false])
            ->addColumn('assessment_id', 'integer', ['signed' => false])
            ->addColumn('justification', 'text')
            ->addColumn('status', 'enum', ['values' => ['pending', 'reviewed', 'approved', 'rejected']], ['default' => 'pending'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('student_id', 'users', 'id')
            ->addForeignKey('assessment_id', 'assessments', 'id')
            ->create();
    }
}
