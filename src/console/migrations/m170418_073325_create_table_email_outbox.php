<?php

use yii\db\Migration;

class m170418_073325_create_table_email_outbox extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%email_outbox}}', [
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
            'status' => $this->smallInteger(1)->notNull()->defaultValue('1'),
            'ref_id' => $this->integer(11)->unsigned(),
            'date_queued' => $this->dateTime()->notNull(),
            'date_sent' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'attempts' => $this->smallInteger(1)->notNull()->defaultValue('0'),
            'created_by' => $this->integer(11)->unsigned(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        echo "m170418_073325_create_table_email_outbox cannot be reverted.\n";
        return false;
    }
}
