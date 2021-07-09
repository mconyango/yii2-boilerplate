<?php

use backend\modules\conf\settings\SmsSettings;
use common\helpers\Lang;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $model SmsSettings */

$this->title = 'SMS Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/conf/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_tab'); ?>
        <div class="tab-content padding-top-10">
            <?php
            $form = ActiveForm::begin([
                'id' => 'sms-settings-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                    'horizontalCssClasses' => [
                        'label' => 'col-md-2',
                        'offset' => 'col-md-offset-2',
                        'wrapper' => 'col-md-6',
                        'error' => '',
                        'hint' => '',
                    ],
                ],
            ]);
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= $this->title; ?></h3>
                </div>
                <div class="panel-body">
                    <?= Html::errorSummary($model, ['class' => 'alert alert-danger']); ?>
                    <?= $form->field($model, SmsSettings::KEY_BASE_URL) ?>
                    <?= $form->field($model, SmsSettings::KEY_DEFAULT_SENDER_ID) ?>
                    <?= $form->field($model, SmsSettings::KEY_USERNAME) ?>
                    <?= $form->field($model, SmsSettings::KEY_PASSWORD)->passwordInput() ?>
                    <?= $form->field($model, SmsSettings::KEY_API_KEY) ?>
                    <?= $form->field($model, SmsSettings::KEY_CLIENT_ID) ?>

                </div>
            </div>
            <div class="panel-footer clearfix">
                <div class="pull-right">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-check"></i>
                        <?= Lang::t('Save Changes') ?>
                    </button>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>