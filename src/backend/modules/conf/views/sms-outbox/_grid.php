<?php

use common\widgets\grid\GridView;
use backend\modules\conf\models\SmsOutbox;

/* @var $this yii\web\View */
/* @var $filterOptions array */
/* @var $model SmsOutbox */
?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => false],
    'showExportButton' => false,
    'columns' => [
        [
            'attribute' => 'org_id',
            'value' => function (SmsOutbox $model) {
                return $model->getRelationAttributeValue('org', 'name');
            },
            'visible' => !\backend\modules\auth\Session::isOrganization(),
        ],
        [
            'attribute' => 'msisdn',
        ],
        [
            'attribute' => 'send_status',
            'value' => function (SmsOutbox $model) {
                return SmsOutbox::decodeSendStatus($model->send_status);
            },
            'filter' => SmsOutbox::sendStatusOptions(),
        ],
        [
            'attribute' => 'response_code',
            'filter' => false,
        ],
        [
            'attribute' => 'created_at',
        ],
        [
            'attribute' => 'response_remarks',
            'visible' => false,
            'filter' => false,
        ],
        [
            'class' => \common\widgets\grid\ActionColumn::class,
            'template' => '{resend}',
            'buttons' => [
                'resend' => function ($url,SmsOutbox $model, $key) {
                    return \yii\helpers\Html::a('Resend', '#', [
                        'class' => 'grid-update',
                        'data-href' => $url,
                        'data-data-type'=>'json',
                        'data-grid'=>$model->getPjaxWidgetId(),
                        'data-confirm-message'=>\common\helpers\Lang::t('Do you want to resend this SMS?')
                    ]);
                }
            ],
        ],
    ],
]);
?>
