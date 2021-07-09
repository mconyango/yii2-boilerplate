<?php

use backend\modules\conf\settings\PasswordSettings;
use common\helpers\Lang;
use common\widgets\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model backend\modules\conf\settings\PasswordSettings */

$this->title = 'Password Settings';
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
                        <?= $form->field($model, PasswordSettings::KEY_USE_PRESET)->checkbox()->hint(''); ?>
                        <div class="col-md-offset-2 col-md-6">
                            <p class="help-block">
                                Includes 5 presets (<strong>simple, normal, fair, medium, and strong</strong>). Instead
                                of
                                setting each
                                parameter below, you can call a preset which will auto-set each of the parameters below.
                            </p>
                            <br/>
                        </div>
                        <div class="clearfix"></div>
                        <?= $form->field($model, PasswordSettings::KEY_PRESET)->widget(Select2::class, [
                            'data' => $model::presetOptions(),
                            'theme' => Select2::THEME_BOOTSTRAP,
                            'pluginOptions' => [
                                'allowClear' => false
                            ],
                        ]) ?>
                        <?= $form->field($model, PasswordSettings::KEY_MIN_LENGTH)->textInput(['type' => 'number', 'step' => 1, 'min' => '4', 'max' => '20']); ?>
                        <?= $form->field($model, PasswordSettings::KEY_MAX_LENGTH)->textInput(['type' => 'number', 'step' => 1, 'min' => '4', 'max' => '20']); ?>
                        <?= $form->field($model, PasswordSettings::KEY_MIN_LOWER)->hint('')->textInput(['type' => 'number', 'min' => '0', 'max' => '20']); ?>
                        <?= $form->field($model, PasswordSettings::KEY_MIN_UPPER)->hint('')->textInput(['type' => 'number', 'min' => '0', 'max' => '20']); ?>
                        <?= $form->field($model, PasswordSettings::KEY_MIN_DIGIT)->hint('')->textInput(['type' => 'number', 'min' => '0', 'max' => '20']); ?>
                        <?= $form->field($model, PasswordSettings::KEY_MIN_SPECIAL)->hint('')->textInput(['type' => 'number', 'min' => '0', 'max' => '20']); ?>
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
    </div>
<?php
$options = [];
$this->registerJs("MyApp.modules.conf.initPasswordSettings(" . Json::encode($options) . ");");
?>