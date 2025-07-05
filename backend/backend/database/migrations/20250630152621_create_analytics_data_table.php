<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAnalyticsDataTable extends AbstractMigration
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
        $this->table('analytics_data', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('student_id', 'integer',  ['signed' => false])
            ->addColumn('course_id', 'integer',  ['signed' => false])
            ->addColumn('total_mark', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('final_exam_mark', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('overall_percentage', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('rank', 'integer')
            ->addColumn('percentile', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('risk_level', 'enum', ['values' => ['low', 'medium', 'high']])
            ->addForeignKey('student_id', 'users', 'id')
            ->addForeignKey('course_id', 'courses', 'id')
            ->create();
    }
}
