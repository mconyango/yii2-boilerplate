<?php

use backend\widgets\Alert;
use common\helpers\Lang;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\modules\auth\forms\ResetPasswordForm */

$this->title = Lang::t('Reset password');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-sm-offset-4 col-sm-4">
        <?= Alert::widget(); ?>
        <div class="well no-padding">
            <?php
            $form = ActiveForm::begin([
                'id' => 'reset-password-form',
                'options' => [
                    'class' => 'smart-form client-form',
                ],
                // 'enableClientValidation' => false,
                'enableAjaxValidation' => false,
            ]);
            ?>
            <header>
                <?= Lang::t('Reset Password') ?>
            </header>

            <fieldset>
                <?= Html::errorSummary($model, ['class' => 'alert alert-danger']) ?>
                <section>
                    <?= Html::activeLabel($model, 'password', ['class' => 'label']); ?>
                    <label class="input"> <i class="icon-append fa fa-lock"></i>
                        <?= Html::activeHiddenInput($model, 'username'); ?>
                        <?= Html::activePasswordInput($model, 'password', ['class' => '']); ?>
                        <p class="help-block text-muted">
                            At least 8 characters with at least 1 number,1 lower case and 1 upper case letter.
                        </p>
                    </label>
                </section>

                <section>
                    <?= Html::activeLabel($model, 'confirm', ['class' => 'label']); ?>
                    <label class="input"> <i class="icon-append fa fa-lock"></i>
                        <?= Html::activePasswordInput($model, 'confirm', ['class' => '']); ?>
                    </label>
                </section>
            </fieldset>

            <footer>
                <div class="row padding-10">
                    <div class="col-md-6">
                        <?= Html::submitButton(Lang::t('Save'), ['class' => 'btn btn-primary btn-block']) ?>
                    </div>
                    <div class="col-md-6">
                        <a class="btn btn-default btn-link"
                           href="<?= \common\helpers\Url::to(['login']) ?>"><?= Lang::t('Login page') ?></a>
                    </div>
                </div>
            </footer>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>