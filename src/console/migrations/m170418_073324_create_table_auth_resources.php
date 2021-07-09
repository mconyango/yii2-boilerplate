<?php

use yii\db\Migration;

class m170418_073324_create_table_auth_resources extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%auth_resources}}', [
            'id' => $this->string(30)->notNull()->append('PRIMARY KEY'),
            'name' => $this->string(60)->notNull(),
            'viewable' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'creatable' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'editable' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'deletable' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'executable' => $this->smallInteger(1)->notNull()->defaultValue('0'),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_auth_resources cannot be reverted.\n";
        return false;
    }
}
