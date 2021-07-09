<?php

use yii\db\Migration;

class m170418_073324_create_table_auth_users extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%auth_users}}', [
            'id' => $this->integer(11)->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'name' => $this->string(64)->notNull(),
            'username' => $this->string(30)->notNull(),
            'phone' => $this->string(15),
            'email' => $this->string(255)->notNull(),
            'status' => $this->smallInteger(6)->notNull()->defaultValue('1'),
            'timezone' => $this->string(60),
            'password_hash' => $this->string(255)->notNull(),
            'password_reset_token' => $this->string(255),
            'auth_key' => $this->string(255),
            'account_activation_token' => $this->string(255),
            'level_id' => $this->integer(11)->notNull(),
            'role_id' => $this->integer(11),
            'profile_image' => $this->string(255),
            'require_password_change' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11),
            'updated_at' => $this->timestamp(),
            'updated_by' => $this->integer(11),
            'is_deleted' => $this->smallInteger(1)->defaultValue('0'),
            'deleted_at' => $this->timestamp(),
            'deleted_by' => $this->integer(11),
            'last_login' => $this->timestamp(),
        ], $tableOptions);

        $this->createIndex('username', '{{%auth_users}}', 'username', true);
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_auth_users cannot be reverted.\n";
        return false;
    }
}
