<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAdvisorNotesTable extends AbstractMigration
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
        $this->table('advisor_notes', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('advisor_id', 'integer', ['signed' => false])
            ->addColumn('student_id', 'integer', ['signed' => false])
            ->addColumn('meeting_date', 'date')
            ->addColumn('note', 'text')
            ->addForeignKey('advisor_id', 'users', 'id')
            ->addForeignKey('student_id', 'users', 'id')
            ->create();
    }
}
