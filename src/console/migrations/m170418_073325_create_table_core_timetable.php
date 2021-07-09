<?php

use yii\db\Migration;

class m170418_073325_create_table_core_timetable extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%core_timetable}}', [
            'id' => $this->integer(11)->unsigned()->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'course_id' => $this->integer(11)->unsigned()->notNull(),
            'start_time' => $this->string(30)->notNull(),
            'end_time' => $this->string(30)->notNull(),
            'day' => $this->smallInteger(1)->notNull()->comment('1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday'),
            'type' => $this->smallInteger(1),
            'course_description' => $this->string(255),
            'room' => $this->string(128),
            'teacher' => $this->string(128),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11),
            'updated_at' => $this->timestamp(),
            'updated_by' => $this->integer(11),
        ], $tableOptions);

        $this->addForeignKey('core_timetable_ibfk_1', '{{%core_timetable}}', 'course_id', '{{%core_course}}', 'id');
    }

    public function safeDown()
    {
        echo "m170418_073325_create_table_core_timetable cannot be reverted.\n";
        return false;
    }
}
