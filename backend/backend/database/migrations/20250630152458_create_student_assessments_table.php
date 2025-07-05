<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStudentAssessmentsTable extends AbstractMigration
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
        $this->table('student_assessments', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('student_id', 'integer', ['signed' => false])
            ->addColumn('assessment_id', 'integer', ['signed' => false])
            ->addColumn('obtained_mark', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('student_id', 'users', 'id')
            ->addForeignKey('assessment_id', 'assessments', 'id')
            ->create();
    }
}
