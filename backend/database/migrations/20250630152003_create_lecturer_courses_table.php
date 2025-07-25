<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateLecturerCoursesTable extends AbstractMigration
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
        $this->table('lecturer_courses', ['id' => false])
            ->addColumn('lecturer_id', 'integer', ['signed' => false])  
            ->addColumn('course_id', 'integer', ['signed' => false])    
            ->addForeignKey('lecturer_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('course_id', 'courses', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}
