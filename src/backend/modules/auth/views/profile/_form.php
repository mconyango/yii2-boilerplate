<?php

use backend\modules\auth\Acl;
use backend\modules\auth\models\Users;
use backend\modules\conf\models\Timezone;
use common\helpers\Lang;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */
/* @var $form yii\bootstrap\ActiveForm */
?>
<?php
$can_update = Users::checkPrivilege(Acl::ACTION_UPDATE, $model->level_id, false) && !$model->isMyAccount();

$form = ActiveForm::begin([
    'id' => 'users-form',
    'layout' => 'horizontal',
    'enableClientValidation' => false,
    'options' => ['data-model' => strtolower($model->shortClassName())],
    'fieldConfig' => [
        'enableError' => false,
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-md-3',
            'offset' => 'col-md-offset-3',
            'wrapper' => 'col-md-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-edit"></i> <?= Html::encode($this->title) ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <?= Html::errorSummary([$model], ['class' => 'alert alert-danger']) ?>
                <?= $form->field($model, 'name') ?>
                <?= $form->field($model, 'email')->textInput(['readonly' => false]) ?>
                <?= $form->field($model, 'phone') ?>
                <?= $form->field($model, 'username')->textInput(['readonly' => true]) ?>
                <?= $form->field($model, 'timezone')->dropDownList(Timezone::getListData()) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <?= $this->render('@authModule/views/user/_imageField', ['model' => $model]) ?>
            </div>
            <div class="col-md-2">
                <div class="text-left">
                    <img id="avator" class="editable img-responsive thumbnail img-center"
                         src="<?= $model->getProfileImageUrl(256) ?>">
                    <ul class="list-unstyled text-muteds">
                        <li>
                            <p><?= Lang::t('Current profile image') ?></p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-footer clearfix">
        <div class="pull-right">
            <button class="btn btn-sm btn-primary" type="submit">
                <i class="fa fa-check"></i> <?= Lang::t('Update') ?>
            </button>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
