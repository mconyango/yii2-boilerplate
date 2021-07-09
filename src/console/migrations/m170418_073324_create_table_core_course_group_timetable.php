<?php

use yii\db\Migration;

class m170418_073324_create_table_core_course_group_timetable extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%core_course_group_timetable}}', [
            'id' => $this->integer(11)->unsigned()->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'timetable_id' => $this->integer(11)->unsigned()->notNull(),
            'group_name' => $this->string(128)->notNull(),
            'group_details' => $this->string(255),
            'max_seats' => $this->integer(11)->notNull(),
            'available_seats' => $this->integer(11),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11),
            'updated_at' => $this->timestamp(),
            'updated_by' => $this->integer(11),
        ], $tableOptions);

        $this->addForeignKey('core_course_group_timetable_ibfk_1', '{{%core_course_group_timetable}}', 'timetable_id', '{{%core_timetable}}', 'id');
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_core_course_group_timetable cannot be reverted.\n";
        return false;
    }
}
