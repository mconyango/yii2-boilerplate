<?php

use backend\modules\conf\settings\SystemSettings;
use common\helpers\Lang;
use common\helpers\Url;
use common\widgets\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use backend\modules\conf\models\Timezone;
use backend\modules\core\models\Country;
use common\helpers\Utils;

/* @var $this yii\web\View */
/* @var $model SystemSettings */
$this->title = Lang::t('Settings');
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
                <fieldset>
                    <legend><?= Lang::t('Company details') ?></legend>
                        <?= $form->field($model, SystemSettings::KEY_COMPANY_NAME); ?>
                        <?= $form->field($model, SystemSettings::KEY_APP_NAME); ?>
                        <?= $form->field($model, SystemSettings::KEY_COMPANY_EMAIL); ?>
                    <?= $form->field($model, SystemSettings::KEY_DEFAULT_TIMEZONE)->widget(Select2::class, [
                        'data' => Timezone::getListData(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]) ?>
                    <?= $form->field($model, SystemSettings::KEY_DEFAULT_COUNTRY)->widget(Select2::class, [
                        'data' => Country::getListData(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]) ?>
                    <?= $form->field($model, SystemSettings::KEY_DEFAULT_CURRENCY)->widget(Select2::class, [
                        'data' => \backend\modules\core\models\Currency::getListData(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]) ?>
                </fieldset>
                <fieldset>
                    <legend><?= Lang::t('Display options') ?></legend>
                    <?= $form->field($model, SystemSettings::KEY_PAGINATION_SIZE)->widget(Select2::class, [
                        'data' => Utils::generateIntegersList(10, 500, 10),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]) ?>
                    <?= $form->field($model, SystemSettings::KEY_DEFAULT_THEME)->widget(Select2::class, [
                        'data' => SystemSettings::themeOptions(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]) ?>
                </fieldset>
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