<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCoursesTable extends AbstractMigration
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
        $this->table('courses', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('course_code', 'string', ['limit' => 20])
            ->addColumn('course_name', 'string', ['limit' => 255])
            ->addColumn('semester', 'string', ['limit' => 20])
            ->addColumn('year', 'integer')
            ->addIndex(['course_code'], ['unique' => true])
            ->create();
    }
}
