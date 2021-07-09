<?php

use common\helpers\Lang;
use common\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use vova07\imperavi\Widget;

/* @var $this yii\web\View */
/* @var $model backend\modules\conf\models\EmailTemplate */
/* @var $form yii\bootstrap\ActiveForm */

$form = ActiveForm::begin([
    'id' => 'email-template-form',
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
        <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="panel-body">
        <?= Html::errorSummary($model, ['class' => 'alert alert-danger']); ?>

        <?php if (\backend\modules\auth\Session::isDev()): ?>
            <?= $form->field($model, 'template_id', []); ?>
        <?php endif ?>

        <?= $form->field($model, 'name'); ?>

        <?= $form->field($model, 'subject', [])->textInput([]); ?>

        <?= $form->field($model, 'sender', [])->textInput([]); ?>

        <?= $form->field($model, 'body')->widget(Widget::class, [
            'settings' => [
                'minHeight' => 150,
                'imageManagerJson' => Url::to(['/redactor/fetch-images']),
                'imageUpload' => Url::to(['/redactor/image-upload']),
                'replaceDivs' => false,
                'paragraphize' => true,
                'cleanOnPaste' => true,
                'removeWithoutAttr' => [],
                'plugins' => [
                    'fullscreen',
                    'imagemanager',
                ],
            ],
        ])->hint(Lang::t('NOTE: Please DO NOT remove placeholders (words enclosed with {{}}). You are
                free to reorganize the body template and add other words or html tags but do not remove the
                placeholders'));
        ?>
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
    <?php ActiveForm::end(); ?>
</div>