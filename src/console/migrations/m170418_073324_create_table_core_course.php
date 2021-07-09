<?php

use yii\db\Migration;

class m170418_073324_create_table_core_course extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%core_course}}', [
            'id' => $this->integer(11)->unsigned()->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'code' => $this->string(30)->notNull(),
            'name' => $this->string(255)->notNull(),
            'credits' => $this->double()->notNull(),
            'category_id' => $this->integer(11)->unsigned()->notNull(),
            'entry_requirements' => $this->string(1000),
            'assessment_materials' => $this->string(1000),
            'study_materials' => $this->string(1000),
            'outline' => $this->string(1000),
            'description' => $this->string(255),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11),
        ], $tableOptions);

        $this->addForeignKey('core_course_ibfk_1', '{{%core_course}}', 'category_id', '{{%core_course_category}}', 'id');
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_core_course cannot be reverted.\n";
        return false;
    }
}
