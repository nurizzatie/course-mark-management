<?php
use Phinx\Migration\AbstractMigration;

class CreateAdvisorAndAnalyticsTables extends AbstractMigration
{
    public function change()
    {
        // Table: advisor_students
        $this->table('advisor_students')
            ->addColumn('advisor_id', 'integer', ['signed' => false])
            ->addColumn('student_id', 'integer', ['signed' => false])
            ->addForeignKey('advisor_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('student_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addIndex(['advisor_id', 'student_id'], ['unique' => true])
            ->create();

        // Table: analytics_data
        $this->table('analytics_data')
            ->addColumn('student_id', 'integer', ['signed' => false])
            ->addColumn('course_id', 'integer', ['signed' => false])
            ->addColumn('total_mark', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('final_exam_mark', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('overall_percentage', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('rank', 'integer')
            ->addColumn('percentile', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('risk_level', 'enum', ['values' => ['low', 'medium', 'high']])
            ->addForeignKey('student_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('course_id', 'courses', 'id', ['delete' => 'CASCADE'])
            ->addIndex(['student_id', 'course_id'], ['unique' => true, 'name' => 'student_course_unique'])
            ->create();
    }
}
