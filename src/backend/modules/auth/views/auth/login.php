<?php

use backend\widgets\Alert;
use common\helpers\Lang;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\modules\auth\forms\LoginForm */
?>

<div class="row">
    <div class="col-sm-offset-4 col-sm-4">
        <?= Alert::widget(); ?>
        <div class="well no-padding">
            <?php
            $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => [
                    'class' => 'smart-form client-form',
                ],
                'enableClientValidation' => false,
                'enableAjaxValidation' => false,
            ]);
            ?>
            <header>
                <?= Lang::t('Sign In') ?>
            </header>
            <fieldset>
                <?= Html::errorSummary($model, ['class' => 'alert alert-danger']) ?>

                <section>
                    <?php if ($model->scenario === 'lwe'): ?>
                        <?= Html::activeLabel($model, 'email', ['class' => 'label']); ?>
                        <label class="input"> <i class="icon-append fa fa-user"></i>
                            <?= Html::activeTextInput($model, 'email', ['class' => '']); ?>
                        </label>
                    <?php else: ?>
                        <?= Html::activeLabel($model, 'username', ['class' => 'label']); ?>
                        <label class="input"> <i class="icon-append fa fa-user"></i>
                            <?= Html::activeTextInput($model, 'username', ['class' => '']); ?>
                        </label>
                    <?php endif ?>
                </section>

                <section>
                    <?= Html::activeLabel($model, 'password', ['class' => 'label']); ?>
                    <label class="input"> <i class="icon-append fa fa-lock"></i>
                        <?= Html::activePasswordInput($model, 'password', ['class' => '']); ?>
                    </label>

                    <div class="note">
                        <a href="<?= Url::to(['request-password-reset']) ?>"><?= Lang::t('Forgot password?') ?></a>
                    </div>
                </section>
                <?php if ($model->getIsVerifyRobotRequired()) : ?>
                    <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                        'captchaAction' => ['captcha'],
                        'template' => '{image}{input}',
                    ]) ?>
                <?php endif; ?>
            </fieldset>
            <footer>
                <button type="submit" class="btn btn-primary"><?= Lang::t('Sign in'); ?></button>
            </footer>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
