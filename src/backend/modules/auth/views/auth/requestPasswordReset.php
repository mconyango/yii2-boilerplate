<?php


use backend\widgets\Alert;
use common\helpers\Lang;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\modules\auth\forms\PasswordResetRequestForm */

$this->title = Lang::t('Request password reset');
?>

<div class="row">
    <div class="col-md-offset-4 col-md-4">
        <?= Alert::widget(); ?>
        <div class="well no-padding">
            <?php
            $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => [
                    'class' => 'smart-form client-form',
                ],
                // 'enableClientValidation' => false,
                'enableAjaxValidation' => false,
            ]);
            ?>
            <header>
                <?= Lang::t('Request Reset Password') ?>
            </header>
            <fieldset>
                <?= Html::errorSummary($model, ['class' => 'alert alert-danger']) ?>
                <section>
                    <?= Html::activeLabel($model, 'email', ['class' => 'label']); ?>
                    <label class="input"> <i class="icon-append fa fa-lock"></i>
                        <?= Html::activeTextInput($model, 'email', ['class' => '']); ?>
                    </label>
                </section>
            </fieldset>
            <footer>
                <div class="row padding-10">
                    <div class="col-md-6">
                        <?= Html::submitButton(Lang::t('Send'), ['class' => 'btn btn-primary btn-block']) ?>
                    </div>
                    <div class="col-md-6">
                        <a class="btn btn-default btn-link"
                           href="<?= \common\helpers\Url::to(['login']) ?>"><?= Lang::t('Back to login page') ?></a>
                    </div>
                </div>
            </footer>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>