<?php

use yii\db\Migration;

class m170418_073325_create_table_email_outbox_queue extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%email_outbox_queue}}', [
            'id' => $this->integer(11)->unsigned()->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
            'message' => $this->text()->notNull(),
            'subject' => $this->string(255),
            'sender_name' => $this->string(60),
            'sender_email' => $this->string(128)->notNull(),
            'recipient_email' => $this->string(128)->notNull(),
            'attachment' => $this->string(1000),
            'attachment_mime_type' => $this->string(128),
            'cc' => $this->string(1000),
            'bcc' => $this->string(1000),
            'type' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'ref_id' => $this->integer(11)->unsigned(),
            'attempts' => $this->smallInteger(1)->notNull()->defaultValue('0'),
            'scheduled' => $this->smallInteger(1)->notNull()->defaultValue('0'),
            'pop_key' => $this->string(128),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11)->unsigned(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073325_create_table_email_outbox_queue cannot be reverted.\n";
        return false;
    }
}
