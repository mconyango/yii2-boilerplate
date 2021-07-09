<?php
/**
 * Created by PhpStorm.
 * @author Fred <mconyango@gmail.com>
 * Date: 2018-07-08
 * Time: 13:14
 */

namespace console\jobs;


use backend\modules\auth\Session;
use backend\modules\conf\models\EmailOutbox;
use backend\modules\conf\settings\EmailSettings;
use common\helpers\DateUtils;
use Exception;
use Yii;
use yii\base\BaseObject;
use yii\mail\MessageInterface;
use yii\queue\Queue;
use yii\swiftmailer\Mailer;
use yii\web\HttpException;

class SendEmailJob extends BaseObject implements JobInterface
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $subject;
    /**
     * @var string
     */
    public $sender_name;
    /**
     * @var string
     */
    public $sender_email;
    /**
     * @var string
     */
    public $recipient_email;

    /**
     * @var string
     */
    public $attachment;

    /**
     * @var string
     */
    public $cc;
    /**
     * @var string
     */
    public $bcc;

    /**
     * @var int
     */
    public $template_id;

    /**
     * @var int
     */
    public $ref_id;

    /**
     * @var string
     */
    public $created_at;

    /**
     * @var int
     */
    public $created_by;

    /**
     * @var string
     */
    public $attempts = 1;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    public $password;

    public $port;

    /**
     * @var string
     */
    public $security;
    const MAX_ATTEMPTS = 3;

    public function init()
    {
        parent::init();
    }


    /**
     * @param Queue $queue which pushed and is handling the job
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @throws HttpException
     */
    public function execute($queue)
    {
        if (empty($this->id)) {
            $template = EmailSettings::getTheme();
            if (empty($template) || !strpos($template, '{{content}}'))
                $template = '{{content}}';

            $this->message = strtr($template, [
                '{{content}}' => $this->message,
                '{{subject}}' => $this->subject,
            ]);
        }
        /* @var $mailer Mailer */
        $mailer = Yii::createObject([
            'class' => Mailer::class,
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => !empty($this->host) ? $this->host : EmailSettings::getHost(),
                'username' => !empty($this->username) ? $this->username : EmailSettings::getUsername(),
                'password' => !empty($this->password) ? $this->password : EmailSettings::getPassword(),
                'port' => !empty($this->port) ? $this->port : EmailSettings::getPort(),
                'encryption' => !empty($this->security) ? $this->security : EmailSettings::getSecurity(),
            ],
        ]);

        //send email
        /* @var $email MessageInterface */
        $email = $mailer->compose(null, []);
        if (!empty($this->attachment)) {
            $mime_type = mime_content_type($this->attachment);
            $email->attach($this->attachment, ['contentType' => $mime_type]);
        }
        if (!empty($this->cc)) {
            $email->setCc(explode(',', $this->cc));
        }
        if (!empty($this->bcc)) {
            $email->setBcc(explode(',', $this->bcc));
        }

        $success = false;
        try {
            $success = $email
                ->setFrom([$this->sender_email => $this->sender_name])
                ->setTo($this->recipient_email)
                ->setSubject($this->subject)
                ->setHtmlBody($this->message)
                ->send($mailer);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }

        $outboxModel = !empty($this->id) ? EmailOutbox::loadModel($this->id) : new EmailOutbox();
        $outboxModel->message = $this->message;
        $outboxModel->subject = $this->subject;
        $outboxModel->sender_email = $this->sender_email;
        $outboxModel->sender_name = $this->sender_name;
        $outboxModel->recipient_email = $this->recipient_email;
        $outboxModel->attachment = $this->attachment;
        $outboxModel->cc = $this->cc;
        $outboxModel->bcc = $this->bcc;
        $outboxModel->template_id = $this->template_id;
        $outboxModel->ref_id = $this->ref_id;
        $outboxModel->status = $success ? EmailOutbox::STATUS_SUCCESS : EmailOutbox::STATUS_FAILED;
        $outboxModel->date_queued = $this->created_at;
        $outboxModel->date_sent = DateUtils::mysqlTimestamp();
        $outboxModel->created_by = $this->created_by;
        $outboxModel->attempts = $this->attempts;

        $outboxModel->save(false);

        if (!$success && $this->attempts < self::MAX_ATTEMPTS) {
            $this->id = $outboxModel->id;
            $this->attempts++;
            static::push($this);
        }
    }


    /**
     * @param mixed $params
     * @return null|string
     * @throws HttpException
     */
    public static function push($params)
    {
        try {
            /* @var $queue \yii\queue\cli\Queue */
            $queue = Yii::$app->queue;
            if ($params instanceof self) {
                $obj = $params;
            } else {
                if (empty($params['created_by']) && Yii::$app instanceof \yii\web\Application)
                    $params['created_by'] = Session::userId();
                if (empty($params['created_at']))
                    $params['created_at'] = DateUtils::mysqlTimestamp();
                $obj = new self($params);
            }

            $id = $queue->push($obj);

            return $id;
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            throw new HttpException(500, $e->getMessage());
        }
    }
}