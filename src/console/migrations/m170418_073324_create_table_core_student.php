<?php

use yii\db\Migration;

class m170418_073324_create_table_core_student extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%core_student}}', [
            'id' => $this->integer(11)->unsigned()->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'number' => $this->string(30)->notNull(),
            'name' => $this->string(128)->notNull(),
            'total_credits' => $this->double()->notNull()->defaultValue('0'),
            'details' => $this->string(500),
            'email' => $this->string(128)->notNull(),
            'user_id' => $this->integer(11)->unsigned()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11),
            'updated_at' => $this->timestamp(),
            'updated_by' => $this->integer(11),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_core_student cannot be reverted.\n";
        return false;
    }
}
