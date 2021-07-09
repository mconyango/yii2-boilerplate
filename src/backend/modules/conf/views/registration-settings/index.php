<?php

use backend\modules\conf\settings\RegistrationSettings;
use common\helpers\Lang;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model RegistrationSettings */

$this->title = 'Registration Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/conf/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
        <?php
        $form = ActiveForm::begin([
            'id' => 'settings-form',
            'layout' => 'horizontal',
            'enableClientValidation' => false,
            'enableAjaxValidation' => false,
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
                <?= $form->field($model, RegistrationSettings::KEY_MIN_AGE)->textInput(['type' => 'number', 'step' => 1, 'min' => 1, 'max' => 120]); ?>
                <?= $form->field($model, RegistrationSettings::KEY_KIN_AGE_LIMIT)->textInput(['type' => 'number', 'step' => 1, 'min' => 1, 'max' => 120]); ?>
            </div>
            <div class="panel-footer clearfix">
                <div class="pull-right">
                    <button class="btn btn-primary" type="submit"><i
                                class="fa fa-check"></i> <?= Lang::t('Save Changes') ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>