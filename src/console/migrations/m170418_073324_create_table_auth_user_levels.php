<?php

use yii\db\Migration;

class m170418_073324_create_table_auth_user_levels extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%auth_user_levels}}', [
            'id' => $this->integer(11)->notNull()->append('PRIMARY KEY'),
            'name' => $this->string(60)->notNull(),
            'forbidden_items' => $this->string(500),
            'parent_id' => $this->smallInteger(6),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_auth_user_levels cannot be reverted.\n";
        return false;
    }
}
