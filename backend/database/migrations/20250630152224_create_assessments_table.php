<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAssessmentsTable extends AbstractMigration
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
        $this->table('assessments', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('course_id', 'integer', ['signed' => false])
            ->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('max_mark', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('weight_percentage', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('is_final_exam', 'boolean', ['default' => false])
            ->addColumn('created_by', 'integer', ['signed' => false])
            ->addForeignKey('course_id', 'courses', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id')
            ->create();
    }
}
