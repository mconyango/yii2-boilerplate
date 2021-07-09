<?php

use yii\db\Migration;

class m170418_073325_create_table_email_template extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%email_template}}', [
            'id' => $this->string(128)->notNull()->append('PRIMARY KEY'),
            'name' => $this->string(128)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'body' => $this->text()->notNull(),
            'sender' => $this->string(255)->notNull(),
            'comments' => $this->string(255),
            'created_at' => $this->timestamp(),
            'created_by' => $this->integer(11),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073325_create_table_email_template cannot be reverted.\n";
        return false;
    }
}
