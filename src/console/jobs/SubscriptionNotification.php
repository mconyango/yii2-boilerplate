<?php

namespace console\jobs;


use backend\modules\conf\models\EmailTemplate;
use backend\modules\conf\models\Notif;
use backend\modules\conf\models\NotifTypes;
use backend\modules\conf\models\SmsTemplate;
use backend\modules\conf\settings\SystemSettings;
use backend\modules\core\models\OrganizationNotifSettings;
use backend\modules\subscription\models\OrgSubscription;
use common\helpers\DateUtils;
use Yii;
use yii\base\InvalidArgumentException;
use yii\queue\Queue;

class SubscriptionNotification extends BaseNotification implements JobInterface, NotifInterface
{

    const NOTIF_ON_PENDING = 'subscription_pending_notification';
    const NOTIF_ON_ACTIVATION = 'subscription_activation_notification';
    const NOTIF_ON_CANCELLATION = 'subscription_cancellation_notification';
    const NOTIF_ON_EXPIRY = 'subscription_expiry_notification';

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function execute($queue)
    {
        $model = OrgSubscription::loadModel($this->item_id, false);
        if ($model === null) {
            Yii::info('Could not find Org Subscription Id=' . $this->item_id);
            return false;
        }
        // get the org admin to be notified
        $notifType = NotifTypes::loadModel(['template_id' => $this->notif_type_id, 'org_id' => null]);
        $settings = OrganizationNotifSettings::getSettings($model->org_id, $notifType->id);
        //Notif::pushNotif($this->notif_type_id, $this->item_id, $settings->users, $this->created_by);
        Notif::pushNotif($this->notif_type_id, $this->item_id, $settings->users, $this->created_by, $settings->enable_internal_notification, $settings->enable_email_notification, $settings->enable_sms_notification, $settings->email, $settings->phone);

    }

    public static function createSystemNotifications()
    {
        return false;
    }

    /**
     *
     * @param string $template
     * @param string $item_id
     * @param string $notif_type_id
     *
     * @return array|bool
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public static function processInternalTemplate($template, $item_id, $notif_type_id)
    {
        $notifModel = NotifTypes::loadModel($notif_type_id, false);
        if (null === $notifModel) {
            return false;
        }
        switch ($notifModel->template_id) {
            case self::NOTIF_ON_PENDING:
                return self::processOnPendingTemplate($item_id, $template, $notifModel->name);
            case self::NOTIF_ON_ACTIVATION:
                return self::processOnActivationTemplate($item_id, $template, $notifModel->name);
            case self::NOTIF_ON_CANCELLATION:
                return self::processOnCancellationTemplate($item_id, $template, $notifModel->name);
            case self::NOTIF_ON_EXPIRY:
                return self::processOnExpiryTemplate($item_id, $template, $notifModel->name);
            default:
                throw new InvalidArgumentException();

        }
    }

    /**
     * @param NotifTypes $notifType
     * @param string $itemId
     * @return array|bool
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public static function processEmailTemplate($notifType, $itemId)
    {
        $emailTemplateModel = EmailTemplate::loadModel(['template_id' => $notifType->email_template_id, 'org_id' => null], false);

        if (null === $emailTemplateModel) {
            return false;
        }

        $emailParams = [
            'sender_name' => SystemSettings::getAppName(),
            'sender_email' => $emailTemplateModel->sender,
            'template_id' => $notifType->email_template_id,
            'ref_id' => $itemId,
        ];

        switch ($notifType->template_id) {
            case self::NOTIF_ON_PENDING:
                $params = self::processOnPendingTemplate($itemId, $emailTemplateModel->body, $emailTemplateModel->subject);
                break;
            case self::NOTIF_ON_ACTIVATION:
                $params = self::processOnActivationTemplate($itemId, $emailTemplateModel->body, $emailTemplateModel->subject);
                break;
            case self::NOTIF_ON_CANCELLATION:
                $params = self::processOnCancellationTemplate($itemId, $emailTemplateModel->body, $emailTemplateModel->subject);
                break;
            case self::NOTIF_ON_EXPIRY:
                $params = self::processOnExpiryTemplate($itemId, $emailTemplateModel->body, $emailTemplateModel->subject);
                break;
            default:
                throw new InvalidArgumentException();

        }

        $emailParams['subject'] = $params['subject'];
        $emailParams['message'] = $params['message'];

        return $emailParams;
    }

    /**
     * @param NotifTypes $notifType
     * @param string $itemId
     * @return array|bool
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public static function processSmsTemplate($notifType, $itemId)
    {
        $smsTemplateModel = SmsTemplate::loadModel(['code' => $notifType->sms_template_id, 'org_id' => null], false);
        if (null === $smsTemplateModel) {
            return false;
        }

        switch ($notifType->template_id) {
            case self::NOTIF_ON_PENDING:
                return self::processOnPendingTemplate($itemId, $smsTemplateModel->template);
            case self::NOTIF_ON_ACTIVATION:
                return self::processOnActivationTemplate($itemId, $smsTemplateModel->template);
            case self::NOTIF_ON_CANCELLATION:
                return self::processOnCancellationTemplate($itemId, $smsTemplateModel->template);
            case self::NOTIF_ON_EXPIRY:
                return self::processOnExpiryTemplate($itemId, $smsTemplateModel->template);
            default:
                throw new InvalidArgumentException();

        }
    }

    /**
     * @param string $itemId
     * @param string $messageTemplate
     * @param null|string $subjectTemplate
     * @return array|bool
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    private static function processOnPendingTemplate($itemId, $messageTemplate, $subjectTemplate = null)
    {
        $model = OrgSubscription::loadModel($itemId, false);
        if ($model === null) {
            return false;
        }
        $message = strtr($messageTemplate, [
            '{contact_person}' => $model->getRelationAttributeValue('org', 'contact_person'),
            '{org_name}' => $model->getRelationAttributeValue('org', 'name'),
            '{pricing_plan}' => $model->getRelationAttributeValue('pricingPlan', 'name'),
            '{activated_on}' => DateUtils::formatDate($model->activated_on, 'd-M-Y'),
            '{period}' => OrgSubscription::getFormattedPeriod($model->subscription_period, $model->subscription_period_measure),
            '{start_date}' => DateUtils::formatDate($model->start_date, 'd-M-Y'),
            '{end_date}' => DateUtils::formatDate($model->end_date, 'd-M-Y'),
            '{status}' => OrgSubscription::decodeStatus($model->status),
        ]);

        $subject = null;
        if (!empty($subjectTemplate)) {
            $subject = strtr($subjectTemplate, [
                '{pricing_plan}' => $model->getRelationAttributeValue('pricingPlan', 'name'),
            ]);
        }

        return [
            'subject' => $subject,
            'message' => $message,
        ];
    }

    /**
     * @param string $itemId
     * @param string $messageTemplate
     * @param null|string $subjectTemplate
     * @return array|bool
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    private static function processOnActivationTemplate($itemId, $messageTemplate, $subjectTemplate = null)
    {
        $model = OrgSubscription::loadModel($itemId, false);
        if ($model === null) {
            return false;
        }
        $message = strtr($messageTemplate, [
            '{contact_person}' => $model->getRelationAttributeValue('org', 'contact_person'),
            '{org_name}' => $model->getRelationAttributeValue('org', 'name'),
            '{pricing_plan}' => $model->getRelationAttributeValue('pricingPlan', 'name'),
            '{activated_on}' => DateUtils::formatDate($model->activated_on, 'd-M-Y'),
            '{period}' => OrgSubscription::getFormattedPeriod($model->subscription_period, $model->subscription_period_measure),
            '{start_date}' => DateUtils::formatDate($model->start_date, 'd-M-Y'),
            '{end_date}' => DateUtils::formatDate($model->end_date, 'd-M-Y'),
            '{status}' => OrgSubscription::decodeStatus($model->status),
        ]);

        $subject = null;
        if (!empty($subjectTemplate)) {
            $subject = strtr($subjectTemplate, [
                '{pricing_plan}' => $model->getRelationAttributeValue('pricingPlan', 'name'),
            ]);
        }

        return [
            'subject' => $subject,
            'message' => $message,
        ];
    }

    /**
     * @param string $itemId
     * @param string $messageTemplate
     * @param null|string $subjectTemplate
     * @return array|bool
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    private static function processOnCancellationTemplate($itemId, $messageTemplate, $subjectTemplate = null)
    {
        $model = OrgSubscription::loadModel($itemId, false);
        if ($model === null) {
            return false;
        }
        $message = strtr($messageTemplate, [
            '{contact_person}' => $model->getRelationAttributeValue('org', 'contact_person'),
            '{org_name}' => $model->getRelationAttributeValue('org', 'name'),
            '{pricing_plan}' => $model->getRelationAttributeValue('pricingPlan', 'name'),
            '{activated_on}' => DateUtils::formatDate($model->activated_on, 'd-M-Y'),
            '{period}' => OrgSubscription::getFormattedPeriod($model->subscription_period, $model->subscription_period_measure),
            '{start_date}' => DateUtils::formatDate($model->start_date, 'd-M-Y'),
            '{end_date}' => DateUtils::formatDate($model->end_date, 'd-M-Y'),
            '{status}' => OrgSubscription::decodeStatus($model->status),
        ]);

        $subject = null;
        if (!empty($subjectTemplate)) {
            $subject = strtr($subjectTemplate, [
                '{pricing_plan}' => $model->getRelationAttributeValue('pricingPlan', 'name'),
            ]);
        }

        return [
            'subject' => $subject,
            'message' => $message,
        ];
    }

    /**
     * @param string $itemId
     * @param string $messageTemplate
     * @param null|string $subjectTemplate
     * @return array|bool
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    private static function processOnExpiryTemplate($itemId, $messageTemplate, $subjectTemplate = null)
    {
        $model = OrgSubscription::loadModel($itemId, false);
        if ($model === null) {
            return false;
        }
        $message = strtr($messageTemplate, [
            '{contact_person}' => $model->getRelationAttributeValue('org', 'contact_person'),
            '{org_name}' => $model->getRelationAttributeValue('org', 'name'),
            '{pricing_plan}' => $model->getRelationAttributeValue('pricingPlan', 'name'),
            '{activated_on}' => DateUtils::formatDate($model->activated_on, 'd-M-Y'),
            '{period}' => OrgSubscription::getFormattedPeriod($model->subscription_period, $model->subscription_period_measure),
            '{start_date}' => DateUtils::formatDate($model->start_date, 'd-M-Y'),
            '{end_date}' => DateUtils::formatDate($model->end_date, 'd-M-Y'),
            '{status}' => OrgSubscription::decodeStatus($model->status),
        ]);

        $subject = null;
        if (!empty($subjectTemplate)) {
            $subject = strtr($subjectTemplate, [
                '{pricing_plan}' => $model->getRelationAttributeValue('pricingPlan', 'name'),
            ]);
        }

        return [
            'subject' => $subject,
            'message' => $message,
        ];
    }
}