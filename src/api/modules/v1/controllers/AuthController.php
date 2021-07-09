<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/09/26
 * Time: 8:33 PM
 */

namespace api\modules\v1\controllers;

use api\controllers\Controller;
use api\modules\v1\forms\ChangePassword;
use api\modules\v1\forms\LoginForm;
use api\modules\v1\forms\ResetPassword;
use api\modules\v1\models\User;
use backend\modules\auth\forms\PasswordResetRequestForm;
use backend\modules\auth\forms\ResetPasswordForm;
use backend\modules\auth\models\PasswordResetCodes;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class AuthController extends Controller
{
    public function init()
    {
        $this->modelClass = User::class;
        $this->resource = \backend\modules\auth\Constants::RES_USER;

        parent::init();
    }

    public function getUnAuthenticatedActions()
    {
        return ['login', 'activation-code', 'new-password'];
    }

    /**
     * @return LoginForm|array
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $model->load(Yii::$app->request->getBodyParams(), '');
        if (!$model->validate()) {
            // validation errs
            return $model;
        }
        $password = $model->password;

        /* @var $user User */
        $user = User::findByUsername($model->username);
        if ($user === null || !$user->validatePassword($password)) {
            throw new ForbiddenHttpException("Invalid credentials.");
        }

        return [
            'token' => $user->getToken(),
        ];
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionBeginResetPassword()
    {
        $model = new PasswordResetRequestForm();
        $model->attributes = Yii::$app->getRequest()->getBodyParams();
        if ($model->validate()) {
            if ($model->sendEmail()) {
                $msg = 'Check your email for further instructions on how to reset your password.<br/>NOTE: If you do not get an email please check your spams folder and mark it as not spam';
                return ['success' => true, 'message' => $msg];
            } else {
            }
        }

        return ['success' => false, 'error' => $model->getErrors(), 'message' => 'Sorry, we are unable to reset password for email provided.'];
    }

    /**
     * @return ResetPassword|array
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCompleteResetPassword()
    {
        $form = new ResetPassword();
        $token = Yii::$app->request->get('token');
        $form->load(Yii::$app->request->getBodyParams(), '');

        if ($form->validate()) {

            $user = User::findByPasswordResetToken($token);
            if ($user !== null) {
                $status = User::isPasswordResetTokenValid($token);
                if ($status) {
                    $user->setPasswordHash($form->password);
                    $user->password_reset_token = null;
                    $user->save(false);

                    return ['message' => 'password reset successful.'];
                } else {
                    throw new NotFoundHttpException('The token has expired.');
                }
            }
            throw new NotFoundHttpException('The token was not found.');
        }
        return $form;
    }

    public function actionChangePassword()
    {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        $model = new ChangePassword();
        $model->username = $user->username;
        if ($model->load(Yii::$app->request->getBodyParams(), '') && $model->validate()) {
            if ($user->validatePassword($model->old_password)) {
                $user->setPasswordHash($model->new_password);
                $user->require_password_change = false;
                $user->save(false);
                return $this->asJson([
                    'message' => 'password changed successfully',
                    'token' => $user->getToken()
                ]);
            } else {
                $model->addError('old_password', 'The password is incorrect.');
            }
        }
        return $model;
    }

    public function actionActivationCode($username)
    {
        $model = PasswordResetCodes::createCode($username);
        if (empty($model)) {
            return ['success' => false, 'message' => 'Could not create the activation code'];
        } else {
            return ['success' => true, 'message' => 'Activation code successfully created.'];
        }
    }

    /**
     * @param string $token
     * @return array
     * @throws BadRequestHttpException (
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */

    public function actionNewPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token, ['is_api' => true]);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $model->attributes = Yii::$app->getRequest()->getBodyParams();
        if ($model->validate() && $model->resetPassword()) {
            $msg = 'New password saved successfully';
            return ['success' => true, 'message' => $msg];
        }
        Yii::$app->response->statusCode = 400;
        return ['success' => false, 'error' => $model->getErrors()];
    }
}