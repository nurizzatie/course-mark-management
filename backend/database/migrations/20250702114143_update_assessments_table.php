<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateAssessmentsTable extends AbstractMigration
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
        $table = $this->table('assessments');

        // Remove old column
        if ($table->hasColumn('is_final_exam')) {
            $table->removeColumn('is_final_exam');
        }

        // Add new enum column 'type'
        $table->addColumn('type', 'enum', [
            'values' => ['assignment', 'lab', 'quiz', 'activity', 'test', 'final'],
            'default' => 'assignment',
            'null' => false
        ]);

        $table->update();
    }
}
