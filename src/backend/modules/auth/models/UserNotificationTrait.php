<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-05 13:23
 * Time: 13:23
 */

namespace backend\modules\auth\models;


use backend\modules\auth\Session;
use backend\modules\conf\models\EmailTemplate;
use backend\modules\conf\settings\SystemSettings;
use console\jobs\SendEmailJob;
use Yii;

trait UserNotificationTrait
{
    public function sendNewLoginDetailsEmail()
    {
        if (!$this->send_email) {
            return false;
        }

        $template_id = 'user_new_login_details';
        $template = EmailTemplate::loadModel($template_id, false);
        if (null === $template) {
            return false;
        }

        $app_name = SystemSettings::getAppName();
        //placeholders: {{name}}, {app_name}, {{url}}, {{username}}, {{password}},
        $body = strtr($template->body, [
            '{{name}}' => $this->name,
            '{{app_name}}' => $app_name,
            '{{url}}' => Yii::$app->getUrlManager()->createAbsoluteUrl(['/auth/auth/login']),
            '{{username}}' => $this->username,
            '{{password}}' => $this->password,
        ]);

        SendEmailJob::push([
            'message' => $body,
            'subject' => $template->subject,
            'sender_name' => $app_name,
            'sender_email' => $template->sender,
            'recipient_email' => $this->email,
            'template_id' => $template_id,
            'ref_id' => $this->id,
        ]);
    }

    public function sendLoginDetailsEmail()
    {
        if (!$this->send_email) {
            return false;
        }

        $template_id = 'user_login_details';
        $template = EmailTemplate::loadModel(['template_id' => $template_id, 'org_id' => null], false);
        if (Session::isOrganization()) {
            $orgTemp = EmailTemplate::findOne(['template_id' => $template_id, 'org_id' => Session::accountId()]);
            if ($orgTemp !== null) {
                $template = $orgTemp;
            }
        }
        if (null === $template) {
            return false;
        }

        $app_name = SystemSettings::getAppName();
        //placeholders: {{name}}, {app_name}, {{url}}, {{username}}, {{password}},
        $body = strtr($template->body, [
            '{{name}}' => $this->name,
            '{{app_name}}' => $app_name,
            '{{url}}' => Yii::$app->getUrlManager()->createAbsoluteUrl(['/auth/auth/login']),
            '{{username}}' => $this->username,
            '{{password}}' => $this->password,
        ]);

        SendEmailJob::push([
            'message' => $body,
            'subject' => $template->subject,
            'sender_name' => $app_name,
            'sender_email' => $template->sender,
            'recipient_email' => $this->email,
            'template_id' => $template_id,
            'ref_id' => $this->id,
            'org_id' => $this->org_id,
        ]);
    }
}