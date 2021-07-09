<?php

use yii\db\Migration;

class m170418_073324_create_table_conf_jobs extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%conf_jobs}}', [
            'id' => $this->string(30)->notNull()->append('PRIMARY KEY'),
            'last_run' => $this->timestamp(),
            'execution_type' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'is_active' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'threads' => $this->integer(11)->notNull()->defaultValue('0'),
            'max_threads' => $this->integer(11)->notNull()->defaultValue('3'),
            'sleep' => $this->integer(11)->notNull()->defaultValue('5'),
            'start_time' => $this->time(),
            'end_time' => $this->time(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_conf_jobs cannot be reverted.\n";
        return false;
    }
}
