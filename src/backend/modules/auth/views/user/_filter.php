<?php

use backend\modules\auth\models\Users;
use common\helpers\Lang;
use kartik\select2\Select2;
use yii\bootstrap\Html;

/* @var $filterOptions array */
/* @var $model Users */
$url = ['index'];
?>
<?= Html::beginForm($url, 'get', ['class' => '', 'id' => 'grid-filter-form', 'data-grid' => $model->getPjaxWidgetId()]) ?>
    <div class="panel-group" id="accordion" role="tablist">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                    <i class="glyphicon glyphicon-chevron-right"></i> <?= Lang::t('Filters') ?>:
                </a>
            </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse" role="tabpanel">
            <div class="panel-body">
                <div class="row">
                    <?php if (!\backend\modules\auth\Session::isOrganization()): ?>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <?= Html::label($model->getAttributeLabel('org_id'), null, ['class' => 'control-label']) ?>
                                <?= Select2::widget([
                                    'name' => 'org_id',
                                    'value' => $filterOptions['org_id'],
                                    'data' => \backend\modules\core\models\Organization::getListData('id', 'name', '--All--'),
                                    'theme' => Select2::THEME_BOOTSTRAP,
                                    'pluginOptions' => [
                                        'allowClear' => false
                                    ],
                                ]); ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <?= Html::label($model->getAttributeLabel('level_id'), null, ['class' => 'control-label']) ?>
                                <?= Select2::widget([
                                    'name' => 'level_id',
                                    'value' => $filterOptions['level_id'],
                                    'data' => Users::levelIdListData('--All--'),
                                    'theme' => Select2::THEME_BOOTSTRAP,
                                    'pluginOptions' => [
                                        'allowClear' => false
                                    ],
                                ]); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <?= Html::label($model->getAttributeLabel('role_id'), null, ['class' => 'control-label']) ?>
                            <?= Select2::widget([
                                'name' => 'role_id',
                                'value' => $filterOptions['role_id'],
                                'data' => \backend\modules\auth\models\Roles::getListData('id', 'name', '--All--', \backend\modules\auth\Session::isOrganization() ? ['level_id' => \backend\modules\auth\models\UserLevels::LEVEL_ORGANIZATION] : []),
                                'theme' => Select2::THEME_BOOTSTRAP,
                                'pluginOptions' => [
                                    'allowClear' => false
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <?= Html::label($model->getAttributeLabel('status'), null, ['class' => 'control-label']) ?>
                            <?= Select2::widget([
                                'name' => 'status',
                                'value' => $filterOptions['status'],
                                'data' => Users::statusOptions('--All--'),
                                'theme' => Select2::THEME_BOOTSTRAP,
                                'pluginOptions' => [
                                    'allowClear' => false
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <?= Html::label($model->getAttributeLabel('name'), null, ['class' => 'control-label']) ?>
                            <?= Html::textInput('name', $filterOptions['name'], ['class' => 'form-control']) ?>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <?= Html::label($model->getAttributeLabel('username'), null, ['class' => 'control-label']) ?>
                            <?= Html::textInput('username', $filterOptions['username'], ['class' => 'form-control']) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <?= Html::label($model->getAttributeLabel('email'), null, ['class' => 'control-label']) ?>
                            <?= Html::textInput('email', $filterOptions['email'], ['class' => 'form-control']) ?>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <?= Html::label($model->getAttributeLabel('phone'), null, ['class' => 'control-label']) ?>
                            <?= Html::textInput('phone', $filterOptions['phone'], ['class' => 'form-control']) ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <?= Html::label($model->getAttributeLabel('last_login'), null, ['class' => 'control-label']) ?>
                            <?= Html::textInput('from', $filterOptions['from'], ['class' => 'form-control show-datepicker', 'placeholder' => 'From']) ?>
                            <?= Html::textInput('to', $filterOptions['to'], ['class' => 'form-control show-datepicker', 'placeholder' => 'To']) ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <div class="form-group">
                            <button class="btn btn-primary pull-left" type="submit"><?= Lang::t('Go') ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= Html::endForm() ?>