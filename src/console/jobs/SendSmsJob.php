<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 25/10/18
 * Time: 17:00
 */

namespace console\jobs;


use backend\modules\auth\Session;
use backend\modules\conf\models\SmsOutbox;
use backend\modules\conf\settings\SmsSettings;
use common\helpers\DateUtils;
use common\helpers\Msisdn;
use Yii;
use yii\base\BaseObject;
use yii\queue\Queue;
use yii\web\HttpException;

class SendSmsJob extends BaseObject implements JobInterface
{
    /**
     * @var string
     */
    public $sender_id;

    /**
     * @var string
     */
    public $client_id;

    /**
     * @var string
     */
    public $msisdn;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;
    /**
     * @var string
     */
    public $message;
    /**
     * @var string
     */
    public $transaction_id;

    /**
     * @var string
     */
    public $created_at;

    /**
     * @var int
     */
    public $created_by;

    /**
     * @var int
     */
    public $org_id;
    /**
     * @var string
     */
    public $baseUrl;
    /**
     * @var string
     */
    private $_getUrl;

    public $sms_id;

    public function init()
    {
        parent::init();
    }


    /**
     * @param Queue $queue which pushed and is handling the job
     */
    public function execute($queue)
    {
        $this->msisdn = Msisdn::format($this->msisdn);
        try {
            $result = $this->doPost();
            $send_status = null;

            if(!empty($this->sms_id)){
                $model=SmsOutbox::loadModel($this->sms_id);
                $model->attempts += 1;
            } else {
                $model = new SmsOutbox();
                $model->msisdn = $this->msisdn;
                $model->message = $this->message;
                $model->sender_id = $this->sender_id;
                $model->org_id = $this->org_id;
                $model->created_at = $this->created_at;
                $model->created_by = $this->created_by;
            }
            if (!empty($result)) {
                if (isset($result['ResultCode'])) {
                    $model->response_code = $result['ResultCode'];
                    if ($result['ResultCode'] == 0) {
                        $model->send_status = SmsOutbox::SEND_STATUS_SUCCESS;
                    } else {
                        $model->send_status = SmsOutbox::SEND_STATUS_FAILED;
                    }
                }

                if (isset($result['ResultDesc'])) {
                    $model->response_remarks = $result['ResultDesc'];
                }
            }
            $model->save(false);
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            Yii::error($e->getTrace());
        }
    }

    /**
     * @param mixed $params
     * @return mixed
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
        } catch (\Exception $e) {
            Yii::debug($e->getTrace());
            throw new HttpException(500, $e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    protected function doPost()
    {
        $ch = curl_init();
        $payload = json_encode([
            'message' => $this->message,
            'to' => $this->msisdn,
            'from' => $this->sender_id,
            'transactionID' => $this->transaction_id,
            'username' => $this->username,
            'password' => $this->password,
            'clientid' => $this->client_id,
        ]);


        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        curl_close($ch);

        if (isset($error_msg)) {
            Yii::error("SEND SMS EXCEPTION: {$error_msg}\n");
            Yii::$app->controller->stdout("SEND SMS EXCEPTION: {$error_msg}\n");
            Yii::debug("SEND SMS EXCEPTION: {$payload}\n");
            Yii::$app->controller->stdout("SEND SMS EXCEPTION: {$payload}\n");
        }

        return json_decode($output, true);
    }
}