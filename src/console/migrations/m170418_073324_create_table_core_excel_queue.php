<?php

use yii\db\Migration;

class m170418_073324_create_table_core_excel_queue extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%core_excel_queue}}', [
            'id' => $this->integer(11)->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'file_name' => $this->string(255)->notNull(),
            'type' => $this->smallInteger(4)->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11),
            'pop_key' => $this->bigInteger(20),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_core_excel_queue cannot be reverted.\n";
        return false;
    }
}
