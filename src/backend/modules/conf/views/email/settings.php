<?php

use backend\modules\conf\settings\EmailSettings;
use common\helpers\Lang;
use common\widgets\select2\Select2;
use vova07\imperavi\Widget;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model EmailSettings */

$this->title = 'Email Settings';
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
                    <?= $form->field($model, EmailSettings::KEY_HOST); ?>
                    <?= $form->field($model, EmailSettings::KEY_PORT); ?>
                    <?= $form->field($model, EmailSettings::KEY_USERNAME)->hint('e.g noreply@domain.com'); ?>
                    <?= $form->field($model, EmailSettings::KEY_PASSWORD)->passwordInput([])->hint(Lang::t('Password for the username.')); ?>
                    <?= $form->field($model, EmailSettings::KEY_SECURITY)->widget(Select2::class, [
                        'data' => ['' => 'NULL', 'ssl' => 'SSL', 'tls' => 'TLS'],
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'pluginOptions' => [
                            'allowClear' => false
                        ],
                    ]) ?>
                    <?= $form->field($model, EmailSettings::KEY_THEME)->widget(Widget::class, [
                        'settings' => [
                            'minHeight' => 150,
                            'replaceDivs' => false,
                            'paragraphize' => true,
                            'cleanOnPaste' => true,
                            'removeWithoutAttr' => [],
                            'imageManagerJson' => Url::to(['/redactor/fetch-images']),
                            'imageUpload' => Url::to(['/redactor/image-upload']),
                            'plugins' => [
                                'fullscreen',
                                'imagemanager',
                            ],
                        ],
                    ])->hint('Make sure that "{{content}}" placeholder is not removed.');
                    ?>
                </div>
            </div>
            <div class="panel-footer clearfix">
                <div class="pull-right">
                    <button class="btn btn-primary" type="submit"><i
                                class="fa fa-check"></i> <?= Lang::t('Save Changes') ?></button>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>