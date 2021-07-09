<?php

use yii\db\Migration;

class m170418_074336_update_table_core_reservation extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%core_reservation}}', [
            'id' => $this->integer(11)->unsigned()->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'student_id' => $this->integer(11)->unsigned()->notNull(),
            'course_id' => $this->integer(11)->notNull()->comment('this column is included to improve performance.'),
            'timetable_id' => $this->integer(11)->notNull()->comment('this column is included to improve performance.'),
            'course_group_timetable_id' => $this->integer(11)->unsigned()->notNull(),
            'status' => $this->smallInteger(1)->notNull()->comment('1=Reserved, 2=Waiting'),
            'confirmed' => $this->smallInteger(1)->notNull()->comment('1=Confirmed, 0=Not confirmed'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11),
            'confirmed_at' => $this->timestamp(),
            'confirmed_by' => $this->integer(11),
            'updated_at' => $this->timestamp(),
            'updated_by' => $this->integer(11),
            'cancelled' => $this->smallInteger(1)->notNull()->defaultValue('0'),
            'cancelled_at' => $this->timestamp(),
            'cancelled_by' => $this->integer(11),
            'expired' => $this->smallInteger(1)->notNull()->defaultValue('0'),
            'expired_at' => $this->timestamp(),
            'pop_key' => $this->bigInteger(20),
        ], $tableOptions);

        $this->addForeignKey('core_reservation_ibfk_1', '{{%core_reservation}}', 'student_id', '{{%core_student}}', 'id');
        $this->addForeignKey('core_reservation_ibfk_2', '{{%core_reservation}}', 'course_group_timetable_id', '{{%core_course_group_timetable}}', 'id');
    }

    public function safeDown()
    {
        echo "m170418_074336_update_table_core_reservation cannot be reverted.\n";
        return false;
    }
}
