<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use nenad\passwordStrength\PasswordInput;
use common\helpers\Lang;
use common\helpers\Url;

$form = ActiveForm::begin([
    'id' => 'change-password-form',
    'layout' => 'horizontal',
    'enableClientValidation' => false,
    'options' => [],
    'fieldConfig' => [
        'enableError' => false,
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
            <?= Html::errorSummary($model, ['class' => 'alert alert-danger']) ?>

            <?= $form->field($model, 'currentPassword')->passwordInput() ?>

            <?= $form->field($model, 'password')->widget(PasswordInput::class, []) ?>

            <?= $form->field($model, 'confirm')->passwordInput() ?>
        </div>
        <div class="panel-footer clearfix">
            <div class="pull-right">
                <button class="btn btn-sm btn-primary" type="submit">
                    <i class="fa fa-check"></i> <?= Lang::t('Change Password') ?>
                </button>

                <a class="btn btn-default btn-sm hidden"
                   href="<?= Url::getReturnUrl(Url::to(['update', 'id' => $model->id])) ?>">
                    <i class="fa fa-times"></i> <?= Lang::t('Cancel') ?>
                </a>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>