<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateStudentAssessmentsCascade extends AbstractMigration
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
        // First, drop existing foreign keys
        $table = $this->table('student_assessments');

        // Replace with your actual constraint names if different
        $table->dropForeignKey('student_id')
              ->dropForeignKey('assessment_id');

        // Add them back with ON DELETE CASCADE
        $table->addForeignKey('student_id', 'users', 'id', [
                    'delete' => 'CASCADE',
                    'update' => 'NO_ACTION'
                ])
              ->addForeignKey('assessment_id', 'assessments', 'id', [
                    'delete' => 'CASCADE',
                    'update' => 'NO_ACTION'
              ])
              ->update();
    }
}
