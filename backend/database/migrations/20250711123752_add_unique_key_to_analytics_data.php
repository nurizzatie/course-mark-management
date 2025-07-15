<?php

use Phinx\Migration\AbstractMigration;

class AddUniqueKeyToAnalyticsData extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('analytics_data');
        $table->addIndex(['student_id', 'course_id'], [
            'unique' => true,
            'name' => 'student_course_unique'
        ])->update();
    }
}

