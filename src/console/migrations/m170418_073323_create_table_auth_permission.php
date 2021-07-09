<?php

use yii\db\Migration;

class m170418_073323_create_table_auth_permission extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%auth_permission}}', [
            'id' => $this->integer(11)->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'role_id' => $this->integer(11)->notNull(),
            'resource_id' => $this->string(30)->notNull(),
            'can_view' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'can_create' => $this->smallInteger(1)->notNull()->defaultValue('0'),
            'can_update' => $this->smallInteger(1)->notNull()->defaultValue('0'),
            'can_delete' => $this->smallInteger(1)->notNull()->defaultValue('0'),
            'can_execute' => $this->smallInteger(1)->notNull()->defaultValue('1'),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073323_create_table_auth_permission cannot be reverted.\n";
        return false;
    }
}
