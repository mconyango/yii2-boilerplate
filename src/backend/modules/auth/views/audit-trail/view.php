<?php

use backend\modules\auth\models\AuditTrail;
use backend\modules\auth\models\Users;
use common\helpers\DateUtils;
use common\helpers\Lang;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\DetailView;

/* @var $this \yii\web\View */
/* @var $model AuditTrail */

$this->title = Lang::t('Audit Trail #{id}', ['id' => $model->id]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><?= Html::encode($this->title); ?></h4>
</div>
<div class="modal-body">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'id',
            ],
            [
                'attribute' => 'action',
                'value' => AuditTrail::decodeAction($model->action),
            ],
            [
                'attribute' => 'action_description',
            ],
            [
                'attribute' => 'url',
            ],
            [
                'attribute' => 'user_agent',
            ],
            [
                'attribute' => 'ip_address',
            ],
            [
                'attribute' => 'user_id',
                'value' => Users::getFieldByPk($model->user_id, 'name'),
            ],
            [
                'attribute' => 'created_at',
                'value' => DateUtils::formatToLocalDate($model->created_at),
            ],

        ],
    ]) ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Lang::t('Data modification') ?></h3>
        </div>
        <div class="panel-body">
            <div style="max-height: 200px;overflow-y: auto">
                <?= Json::encode(@unserialize($model->details), JSON_PRETTY_PRINT) ?>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><i
                class="fa fa-times"></i> <?= Lang::t('Close') ?></button>
</div>