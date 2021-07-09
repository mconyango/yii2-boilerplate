<?php

use yii\db\Migration;

class m170418_073324_create_table_conf_job_processes extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%conf_job_processes}}', [
            'id' => $this->string(255)->notNull()->append('PRIMARY KEY'),
            'job_id' => $this->string(255)->notNull(),
            'last_run_datetime' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'created_at' => $this->timestamp(),
        ], $tableOptions);

        $this->addForeignKey('fk_conf_job_processes_job_id', '{{%conf_job_processes}}', 'job_id', '{{%conf_jobs}}', 'id');
    }

    public function safeDown()
    {
        echo "m170418_073324_create_table_conf_job_processes cannot be reverted.\n";
        return false;
    }
}
