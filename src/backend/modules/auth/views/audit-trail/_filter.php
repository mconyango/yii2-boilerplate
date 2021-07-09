<?php

use common\helpers\Lang;
use common\helpers\Url;
use common\widgets\select2\Select2;
use yii\bootstrap\Html;
use backend\modules\auth\models\Users;
use backend\modules\auth\models\AuditTrail;

?>
<?= Html::beginForm(Url::to(['index']), 'get', ['class' => '', 'id' => 'grid-filter-form', 'data-grid' => $model->getPjaxWidgetId()]) ?>
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
                <!-- FILTER FOR USER -->
                <div class="col-sm-2">
                    <div class="form-group">
                        <?= Html::label($model->getAttributeLabel('user_id'), "", ['class' => 'control-label']) ?>
                        <?= Select2::widget([
                            'name' => 'user_id',
                            'value' => $filterOptions['user_id'],
                            'data' => Users::getListData('id', 'name', '--All--'),
                            'theme' => Select2::THEME_BOOTSTRAP,
                            'options' => [
                                'id' => Html::getInputId($model, 'user_id'),
                                'data-url' => Url::to(['user/get-list']),
                                'data-selected' => $filterOptions['user_id'],
                            ],
                            'pluginOptions' => [
                                'allowClear' => false
                            ],
                        ]); ?>
                    </div>
                </div>

                <!-- FILTER FOR ACTION -->
                <div class="col-sm-2">
                    <div class="form-group">
                        <?= Html::label($model->getAttributeLabel('action'), "", ['class' => 'control-label']) ?>
                        <?= Select2::widget([
                            'name' => 'action',
                            'value' => $filterOptions['action'],
                            'data' => AuditTrail::actionOptions('--All--'),
                            'theme' => Select2::THEME_BOOTSTRAP,
                            'pluginOptions' => [
                                'allowClear' => false
                            ],
                        ]); ?>
                    </div>
                </div>

                <!-- FILTER FOR DATE(FROM) -->
                <div class="col-sm-2">
                    <div class="form-group">
                        <?= Html::label('From', "", ['class' => 'control-label']) ?>
                        <?= Html::textInput('from', $filterOptions['from'], ['class' => 'form-control show-datepicker']); ?>
                    </div>
                </div>

                <!-- FILTER FOR DATE(TO) -->
                <div class="col-sm-2">
                    <div class="form-group">
                        <?= Html::label('To', "", ['class' => 'control-label']) ?>
                        <?= Html::textInput('to', $filterOptions['to'], ['class' => 'form-control show-datepicker']); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">
                            <?= Lang::t('Submit') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= Html::endForm() ?>

