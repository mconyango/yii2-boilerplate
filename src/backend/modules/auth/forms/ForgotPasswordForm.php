<?php

/**
 * Forgot password form
 */
class ForgotPasswordForm extends FormModel
{

    /**
     * Could be username or email
     * @var type
     */
    public $username;

    /**
     * Success message if validation is passed
     * @var type
     */
    public $success_message;

    /**
     * User model
     * @var UsersOld
     */
    public $user_model;

    /**
     * The context module of this class
     * @var type
     */
    public $context_module = null;

    public function rules()
    {
        return [
            ['username', 'required', 'message' => 'Username or Email is required.'],
            ['username', 'authenticate'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Username or Email',
        ];
    }

    public function beforeValidate()
    {
        $this->getUserModel();
        return parent::beforeValidate();
    }

    public function getUserModel()
    {
        $this->user_model = UsersOld::model()->find('`username`=:t1 OR `email`=:t1', [':t1' => $this->username]);
    }

    public function afterValidate()
    {
        if (!$this->hasErrors()) {
            $this->sendEmail();
            $this->success_message = Lang::t('Check this email ({email}) for instructions on how to get a new password.If you don\'t get email please check your spam and mark it as "not spam"', ['{email}' => $this->user_model->email]);
        }
        return parent::afterValidate();
    }

    public function sendEmail()
    {

        $this->user_model->password_reset_code = Common::generateSalt();
        $this->user_model->password_reset_request_date = new CDbExpression('NOW()');
        $this->user_model->save(false);

        $template = ConfEmailTemplate::model()->getRow('*', '`id`=:t1', [':t1' => ConfEmailTemplate::ID_FORGOT_PASSWORD]);
        if (empty($template))
            return FALSE;

        //placeholders : {name},{link}
        $body = Common::myStringReplace($template['body'], [
            '{{name}}' => $this->user_model->name,
            '{{link}}' => Yii::app()->createAbsoluteUrl('users/auth/resetPassword', ['id' => $this->user_model->id, 'token' => $this->user_model->password_reset_code]),
        ]);

        $email = [
            [
                'message' => $body,
                'subject' => $template['subject'],
                'sender_name' => Yii::app()->settings->get(ConfModuleConstants::SETTINGS_GENERAL, ConfModuleConstants::SETTINGS_APP_NAME, Yii::app()->name),
                'sender_email' => $template['sender'],
                'recipient_email' => $this->user_model->email,
                'type' => EmailQueue::TYPE_PASSWORD_RECOVERY,
                'ref_id' => $this->user_model->id,
            ],
        ];
        EmailQueue::model()->pushToQueue($email);
    }

    public function authenticate()
    {
        if (!$this->hasErrors() && $this->user_model === NULL)
            $this->addError('username', 'No account associated with the Username/Email.');
    }

}
