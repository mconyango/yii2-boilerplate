<?php

use backend\modules\auth\models\Users;
use common\helpers\DateUtils;
use common\helpers\Lang;
use yii\bootstrap\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-2">
        <?= $this->render('_viewOptions', ['model' => $model]) ?>
    </div>
    <div class="col-md-10">
        <?php if ($model->status != Users::STATUS_ACTIVE): ?>
            <div class="alert alert-warning">
                <h5>
                    <i class="fa fa-warning"></i> <?= Lang::t('This account is {status}', ['status' => Users::decodeStatus($model->status)]) ?>
                </h5>
            </div>
        <?php endif; ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= $this->title ?></h3>
            </div>
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table detail-view table-condensed table-bordered table-striped'],
                'attributes' => [
                    [
                        'attribute' => 'id',
                    ],
                    [
                        'attribute' => 'name',
                    ],
                    [
                        'attribute' => 'username',
                    ],
                    [
                        'attribute' => 'email',
                    ],
                    [
                        'attribute' => 'phone',
                    ],
                    [
                        'attribute' => 'timezone',
                    ],
                    [
                        'attribute' => 'status',
                        'value' => Users::decodeStatus($model->status)
                    ],
                    [
                        'attribute' => 'level_id',
                        'value' => $model->getRelationAttributeValue('level', 'name'),
                    ],
                    [
                        'attribute' => 'role_id',
                        'value' => $model->getRelationAttributeValue('role', 'name'),
                    ],
                    [
                        'attribute' => 'branch_id',
                        'value' => $model->getRelationAttributeValue('branch', 'name'),
                    ],
                    [
                        'attribute' => 'require_password_change',
                        'value' => \common\helpers\Utils::decodeBoolean($model->require_password_change),
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => DateUtils::formatToLocalDate($model->created_at),
                    ],
                    [
                        'attribute' => 'last_login',
                        'value' => DateUtils::formatToLocalDate($model->last_login),
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>