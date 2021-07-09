<?php

use backend\modules\auth\models\Resources;
use backend\modules\auth\models\UserLevels;
use common\helpers\Lang;
use common\widgets\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model Resources */
/* @var $controller \backend\controllers\BackendController */
$controller = Yii::$app->controller;
$this->title = $controller->getPageTitle();
?>
<?php
$form = ActiveForm::begin([
    'id' => 'my-modal-form',
    'layout' => 'horizontal',
    'options' => ['data-model' => strtolower($model->shortClassName())],
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-md-3',
            'offset' => 'col-md-offset-3',
            'wrapper' => 'col-md-6',
            'error' => '',
            'hint' => '',
        ],
    ],
]);
?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><?= Html::encode($this->title); ?></h4>
    </div>

    <div class="modal-body">
        <div class="alert hidden" id="my-modal-notif"></div>
        <?= $form->field($model, 'id'); ?>
        <?= $form->field($model, 'name'); ?>
        <?= $form->field($model, 'forbidden_items')->widget(Select2::class, [
            'data' => Resources::getListData('id', 'name'),
            'modal' => true,
            'theme' => Select2::THEME_BOOTSTRAP,
            'options' => ['multiple' => true],
            'pluginOptions' => [
                'allowClear' => false
            ],
        ]) ?>
        <?= $form->field($model, 'parent_id')->widget(Select2::class, [
            'data' => UserLevels::getListData('id', 'name', true),
            'modal' => true,
            'theme' => Select2::THEME_BOOTSTRAP,
            'pluginOptions' => [
                'allowClear' => false
            ],
        ]) ?>
        <?= $form->field($model, 'is_active')->checkbox(); ?>
    </div>
    <div class="modal-footer">
        <button class="btn btn-primary" type="submit"><i
                    class="fa fa-check"></i> <?= Lang::t($model->isNewRecord ? 'Create' : 'Save changes') ?></button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i
                    class="fa fa-times"></i> <?= Lang::t('Close') ?></button>
    </div>
<?php ActiveForm::end(); ?>