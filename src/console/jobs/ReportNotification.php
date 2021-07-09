<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-07-01
 * Time: 2:56 AM
 */

namespace console\jobs;


use backend\modules\conf\models\Notif;
use backend\modules\reports\models\AdhocReport;
use Yii;
use yii\queue\Queue;

class ReportNotification extends BaseNotification implements JobInterface, NotifInterface
{

    const NOTIF_REPORT_COMPLETION = 'report_builder_completion';
    const NOTIF_REPORT_ERROR = 'report_builder_completion';

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function execute($queue)
    {
        $model = AdhocReport::loadModel($this->item_id, false);
        Notif::pushNotif($this->notif_type_id, $this->item_id, [$model->created_by], $this->created_by);
    }

    public static function createSystemNotifications()
    {
        return false;
    }

    /**
     * @param string $itemId
     * @param string $messageTemplate
     * @param null|string $subjectTemplate
     * @return array|bool
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public static function processTemplate($itemId, $messageTemplate, $subjectTemplate = null)
    {
        //placeholders:{status},{url}
        $model = AdhocReport::loadModel($itemId, false);
        if ($model === null)
            return false;
        $admin = $model->getRelationAttributeValue('createdByUser', 'name');
        $url = Yii::$app->getUrlManager()->createAbsoluteUrl(['/reports/adhoc-report/index',  'status' => $model->status]);
        $status = ($model->status == AdhocReport::STATUS_ERROR) ? 'Has Errors' : 'Success';
        $message = strtr($messageTemplate, [
            '{name}' => $model->name,
            '{status}' => $status,
            '{url}' => $url,
            '{created_by}' => $admin,
            '{report_file}' => $model->report_file,
        ]);

        $subject = null;
        if (!empty($subjectTemplate)) {
            $subject = strtr($subjectTemplate, [
                '{name}' => $model->name,
                '{status}' => $status,
            ]);
        }

        return [
            'subject' => $subject,
            'message' => $message,
            'url' => $url,
        ];
    }

    public static function processEmailTemplate($template_id, $item_id, $notif_type_id)
    {
        // TODO: Implement processEmailTemplate() method.
    }

    public static function processInternalTemplate($template, $item_id, $notif_type_id)
    {
        // TODO: Implement processInternalTemplate() method.
    }

    public static function processSmsTemplate($template_id, $item_id, $notif_type_id)
    {
        // TODO: Implement processSmsTemplate() method.
    }
}