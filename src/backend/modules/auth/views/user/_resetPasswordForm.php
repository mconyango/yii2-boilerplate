<?php

use common\helpers\Lang;
use common\helpers\Url;
use kartik\password\PasswordInput;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model \backend\modules\auth\models\Users */
?>
    <div class="row">
        <div class="col-xs-12">
            <?php
            $form = ActiveForm::begin([
                'id' => 'reset-password-form',
                'layout' => 'horizontal',
                'enableAjaxValidation' => false,
                'enableClientValidation' => false,
                'options' => [],
                'fieldConfig' => [
                    'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                    'horizontalCssClasses' => [
                        'label' => 'col-md-2',
                        'offset' => 'col-md-offset-2',
                        'wrapper' => 'col-md-4',
                        'error' => '',
                        'hint' => '',
                    ],
                ],
            ]);
            ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-lock"></i> <?= Html::encode($this->title) ?></h3>
                </div>
                <div class="panel-body">
                    <?= Html::errorSummary($model, ['class' => 'alert alert-danger']) ?>
                    <?= $form->field($model, 'auto_generate_password')->checkbox() ?>
                    <div id="password-fields-wrapper">
                        <?= $form->field($model, 'password')->widget(PasswordInput::class, [
                            'pluginOptions' => [
                                'showMeter' => true,
                                'toggleMask' => true,
                            ],
                            'options' => ['class' => 'form-control disable-copy-paste']
                        ]) ?>
                        <?= $form->field($model, 'confirm')->widget(PasswordInput::class, [
                            'pluginOptions' => [
                                'showMeter' => false,
                                'toggleMask' => true,
                            ],
                            'options' => ['class' => 'form-control disable-copy-paste']
                        ]) ?>
                    </div>
                    <?= $form->field($model, 'require_password_change')->checkbox() ?>
                    <?= $form->field($model, 'send_email')->checkbox() ?>
                </div>
                <div class="panel-footer clearfix">
                    <div class="pull-right">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa fa-check"></i> <?= Lang::t('Reset Password') ?>
                        </button>

                        <a class="btn btn-default"
                           href="<?= Url::getReturnUrl(Url::to(['view', 'id' => $model->id])) ?>">
                            <i class="fa fa-times"></i> <?= Lang::t('Cancel') ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
<?php
$options = [];
$this->registerJs(" MyApp.modules.auth.autoGeneratePassword(" . Json::encode($options) . ");");
?>