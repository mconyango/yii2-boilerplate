<?php

use backend\modules\conf\settings\GoogleMapSettings;
use common\helpers\Lang;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $model GoogleMapSettings */
$this->title = Lang::t('Google Map Settings');
$this->params['breadcrumbs'] = [
    $this->title
];
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
                <?= $form->field($model, GoogleMapSettings::KEY_API_KEY); ?>
                <?= $form->field($model, GoogleMapSettings::KEY_DEFAULT_MAP_CENTER); ?>
            </div>
            <div class="panel-footer clearfix">
                <div class="pull-right">
                    <button class="btn btn-primary" type="submit">
                        <?= Lang::t('Save Changes') ?>
                    </button>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>