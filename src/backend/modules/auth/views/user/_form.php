<?php

use backend\modules\auth\models\Users;
use common\helpers\Lang;
use common\helpers\Url;
use common\widgets\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\password\PasswordInput;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */
/* @var $form yii\bootstrap\ActiveForm */
?>
<?php
$form = ActiveForm::begin([
    'id' => 'user-form',
    'layout' => 'horizontal',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-md-4',
            'offset' => 'col-md-offset-4',
            'wrapper' => 'col-md-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);
?>
    <div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?= Html::encode($this->title) ?>
        </h3>
    </div>
    <div class="panel-body">
        <?= Html::errorSummary([$model], ['class' => 'alert alert-danger']) ?>
        <?php if ($model->checkPermission(false, true)) : ?>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'level_id')->widget(Select2::class, [
                    'data' => Users::levelIdListData(),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => [
                        'class' => 'form-control parent-depdropdown',
                        'data-child-selectors' => [
                            '#' . Html::getInputId($model, 'role_id'),
                        ],
                    ],
                    'pluginOptions' => [
                        'allowClear' => false
                    ],
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'role_id')->widget(Select2::class, [
                    'data' => \backend\modules\auth\models\Roles::getListData('id', 'name', false, []),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => [
                        'data-url' => Url::to(['role/get-list', 'level_id' => 'idV']),
                        'data-selected' => $model->role_id,
                    ],
                    'pluginOptions' => [
                        'allowClear' => false
                    ],
                ]) ?>
            </div>
            <hr/>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'name') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'email') ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'phone') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'timezone')->widget(Select2::class, [
                                'data' => \backend\modules\conf\models\Timezone::getListData(),
                                'theme' => Select2::THEME_BOOTSTRAP,
                                'pluginOptions' => [
                                    'allowClear' => false
                                ],
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $this->render('_imageField', ['model' => $model]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'require_password_change')->checkbox() ?>
                            <?php if ($model->isNewRecord): ?>
                                <?= $form->field($model, 'send_email')->checkbox() ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'username') ?>
                            <?php if ($model->isNewRecord): ?>
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
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer clearfix">
            <div class="pull-right">
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-check"></i> <?= Lang::t($model->isNewRecord ? 'Create' : 'Save Changes') ?>
                </button>
                <a class="btn btn-default hidden"
                   href="<?= Url::getReturnUrl($model->isNewRecord ? Url::to(['index', 'level_id' => $model->level_id]) : Url::to(['view', 'id' => $model->id])) ?>">
                    <i class="fa fa-times"></i> <?= Lang::t('Cancel') ?>
                </a>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>
<?php
$options = [
    'levelIdFieldSelector' => '#' . Html::getInputId($model, 'level_id'),
];
$this->registerJs("MyApp.modules.auth.initUserForm(" . Json::encode($options) . ");");
?>