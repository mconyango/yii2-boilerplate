<?php

use yii\db\Migration;

class m170418_073324_create_table_core_student_course extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%core_student_course}}', [
            'id' => $this->integer(11)->unsigned()->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'student_id' => $this->integer(11)->unsigned()->notNull(),
            'course_id' => $this->integer(11)->unsigned()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_core_student_course cannot be reverted.\n";
        return false;
    }
}
