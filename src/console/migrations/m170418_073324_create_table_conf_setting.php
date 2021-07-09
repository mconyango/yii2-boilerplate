<?php

use yii\db\Migration;

class m170418_073324_create_table_conf_setting extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%conf_setting}}', [
            'id' => $this->integer(10)->unsigned()->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'category' => $this->string(255)->notNull(),
            'key' => $this->string(255)->notNull(),
            'value' => $this->text(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_conf_setting cannot be reverted.\n";
        return false;
    }
}
