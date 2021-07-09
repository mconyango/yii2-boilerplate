<?php

use common\helpers\Lang;
use common\helpers\Url;
use kartik\password\PasswordInput;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */
?>

<div class="row">
    <div class="col-xs-12">
        <?php
        $form = ActiveForm::begin([
            'id' => 'change-password-form',
            'layout' => 'horizontal',
            'enableAjaxValidation' => false,
            'enableClientValidation' => false,
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
                <?= $form->field($model, 'currentPassword')->widget(PasswordInput::class, [
                    'pluginOptions' => [
                        'showMeter' => false,
                        'toggleMask' => true,
                    ],
                    'options' => ['class' => 'form-control disable-copy-paste']
                ]) ?>
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
            <div class="panel-footer clearfix">
                <div class="pull-right">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-check"></i> <?= Lang::t('Change Password') ?>
                    </button>
                    <a class="btn btn-default hidden"
                       href="<?= Url::getReturnUrl(Url::to(['update', 'id' => $model->id])) ?>">
                        <i class="fa fa-times"></i> <?= Lang::t('Cancel') ?>
                    </a>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>