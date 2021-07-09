<?php

use yii\db\Migration;

class m170418_073324_create_table_auth_roles extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%auth_roles}}', [
            'id' => $this->integer(11)->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'name' => $this->string(128)->notNull(),
            'description' => $this->string(255)->notNull(),
            'can_access_backend' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'readonly' => $this->smallInteger(1)->notNull()->defaultValue('0'),
            'level_id' => $this->integer(3),
            'created_at' => $this->timestamp(),
            'created_by' => $this->integer(11)->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_auth_roles cannot be reverted.\n";
        return false;
    }
}
