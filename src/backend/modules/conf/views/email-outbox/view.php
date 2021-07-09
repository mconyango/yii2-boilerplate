<?php
use backend\modules\conf\models\EmailOutbox;
use common\helpers\Lang;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this \yii\web\View */
/* @var $model EmailOutbox */

$this->title = Lang::t('Email Outbox #{id}', ['id' => $model->id]);
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
                'attribute' => 'sender_email',
                'value' => strtr('{name} <{email}>', ['{name}' => $model->sender_name, '{email}' => $model->sender_email]),
            ],
            [
                'attribute' => 'recipient_email',
            ],
            [
                'attribute' => 'cc',
                'visible' => !empty($model->cc),
            ],
            [
                'attribute' => 'bcc',
                'visible' => !empty($model->bcc),

            ],
            [
                'attribute' => 'subject',
            ],
            [
                'attribute' => 'date_sent',
                'value' => \common\helpers\DateUtils::formatToLocalDate($model->date_sent),
            ]
        ],
    ]) ?>

    <div class="well well-light" style="max-height: 300px;overflow: auto;">
        <?= Html::decode($model->message); ?>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><i
            class="fa fa-times"></i> <?= Lang::t('Close') ?></button>
</div>