<?php

use backend\modules\auth\models\Roles;
use backend\modules\auth\models\Users;
use backend\modules\auth\Session;
use backend\modules\conf\models\NotifTypes;
use yii\bootstrap\Html;
use common\helpers\Url;
use common\helpers\Lang;
use yii\bootstrap\ActiveForm;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model backend\modules\conf\models\NotifTypes */
/* @var $form yii\bootstrap\ActiveForm */

$form = ActiveForm::begin([
    'id' => 'notif-form',
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
//dd($model->enable_internal_notification)
?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="panel-body">
            <?= Html::errorSummary($model, ['class' => 'alert alert-danger']); ?>
            <fieldset>
                <legend><?= Lang::t('Notification details') ?></legend>
                <?php if (Session::isDev()): ?>
                    <?= $form->field($model, 'template_id', []); ?>
                    <?= $form->field($model, 'name', []); ?>
                    <?= $form->field($model, 'description')->textarea(['rows' => 3]); ?>
                    <?= $form->field($model, 'model_class_name'); ?>
                    <?= $form->field($model, 'notification_trigger')->dropDownList(NotifTypes::notificationTriggerOptions()); ?>
                    <?= $form->field($model, 'max_notifications'); ?>
                    <?= $form->field($model, 'notification_time')->hint('Time in this format: HH:MM e.g 08:00'); ?>
                    <?= $form->field($model, 'fa_icon_class'); ?>
                <?php endif ?>
                <?= $form->field($model, 'enable_internal_notification')->checkbox(); ?>
                <?= $form->field($model, 'template')->textarea(['rows' => 3])->hint(
                    'Template for displaying notification within this system<br>Please do not remove placeholders (terms enclosed in {{}})'
                ); ?>
                <?= $form->field($model, 'enable_email_notification')->checkbox(); ?>
                <?= $form->field($model, 'email_template_id')->dropDownList(\backend\modules\conf\models\EmailTemplate::getListData('template_id', 'name', [])); ?>
                <?= $form->field($model, 'enable_sms_notification')->checkbox(); ?>
                <?= $form->field($model, 'sms_template_id')->dropDownList(\backend\modules\conf\models\SmsTemplate::getListData('code', 'name')); ?>
                <?= $form->field($model, 'is_active')->checkbox(); ?>
            </fieldset>
            <fieldset>
                <legend><?= Lang::t('People to notify') ?></legend>
                <?= $form->field($model, 'notify_all_users')->checkbox(); ?>
                <?= $form->field($model, 'users')->dropDownList(Users::getListData('id', 'name', false, '[[status]]=:t2', [':t2' => Users::STATUS_ACTIVE]), ['multiple' => true, 'class' => 'form-control select2']); ?>
                <?= $form->field($model, 'roles')->dropDownList(Roles::getListData('id', 'name'), ['multiple' => true, 'class' => 'select2']); ?>
                <?= $form->field($model, 'email')->textarea(['class' => 'form-control', 'rows' => 3])->hint('Comma separated email addresses to receive email notification.'); ?>
                <?php //echo $form->field($model, 'phone')->hint('Comma separated phone numbers to receive SMS notification.'); ?>

            </fieldset>
        </div>
        <div class="panel-footer clearfix">
            <div class="pull-right">
                <button class="btn btn-sm btn-primary" type="submit"><i
                            class="fa fa-check"></i> <?= Lang::t($model->isNewRecord ? 'Create' : 'Save changes') ?>
                </button>
                <a class="btn btn-default btn-sm"
                   href="<?= Url::getReturnUrl(Url::to(['index'])) ?>"><i
                            class="fa fa-times"></i> <?= Lang::t('Cancel') ?></a>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>
<?php
$options = [
    'modelClass' => strtolower($model->shortClassName()),
];
$this->registerJs("MyApp.modules.conf.notificationSettings(" . Json::encode($options) . ");");
?>