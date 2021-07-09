<?php

namespace backend\modules\auth\forms;

use backend\modules\auth\models\Users;
use backend\modules\conf\models\EmailTemplate;
use backend\modules\conf\settings\SystemSettings;
use common\models\Model;
use console\jobs\SendEmailJob;
use Yii;

/**
 * Password reset request form.
 */
class PasswordResetRequestForm extends Model
{
    public $email;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => Users::class,
                'filter' => ['status' => Users::STATUS_ACTIVE],
                'message' => 'There is no user with such email.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool Whether the email was send.
     * @throws \yii\web\NotFoundHttpException
     */
    public function sendEmail()
    {
        /* @var $user Users */
        $user = Users::findOne([
            'status' => Users::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if ($user) {
            $user->generatePasswordResetToken();

            if ($user->save(false)) {
                //add email queue here.
                return $this->queueEmail($user);
            }
        }

        return false;
    }

    /**
     * @param Users $user
     * @return bool
     * @throws \yii\web\NotFoundHttpException
     */
    protected function queueEmail($user)
    {
        $template_id = 'user_forgot_password';
        $template = EmailTemplate::loadModel($template_id, false);

        if (null === $template) {
            return false;
        }

        $app_name = SystemSettings::getAppName();
        //placeholders: {{name}},{{url}},
        $message = strtr($template->body, [
            '{{name}}' => $user->name,
            '{{url}}' => Yii::$app->getUrlManager()->createAbsoluteUrl(['/auth/auth/reset-password', 'token' => $user->password_reset_token]),
        ]);

        return SendEmailJob::push([
            'message' => $message,
            'subject' => $template['subject'],
            'sender_name' => $app_name,
            'sender_email' => $template['sender'],
            'recipient_email' => $this->email,
            'template_id' => $template_id,
            'ref_id' => $user->id,
        ]);
    }
}
