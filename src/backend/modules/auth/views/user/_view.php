<?php

use common\widgets\detailView\DetailView;
use common\helpers\Lang;
use backend\modules\auth\models\Users;
use common\helpers\DateUtils;
use common\helpers\Utils;

/* @var $model Users */
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Lang::t('User details') ?></h3>
    </div>
    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table detail-view table-condensed table-bordered table-striped'],
        'attributes' => [
            [
                'attribute' => 'id',
            ],
            [
                'attribute' => 'is_main_account',
                'value' => Utils::decodeBoolean($model->is_main_account),
                'visible' => !empty($model->account_id),
            ],
            [
                'attribute' => 'name',
            ],
            [
                'attribute' => 'email',
            ],
            [
                'attribute' => 'phone',
            ],
            [
                'attribute' => 'username',
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