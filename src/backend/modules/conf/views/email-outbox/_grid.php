<?php

use backend\modules\conf\models\EmailOutbox;
use common\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $model EmailOutbox */
?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'showExportButton' => false,
    'createButton' => ['visible' => false],
    'columns' => [
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'org_id',
            'value' => function (EmailOutbox $model) {
                return $model->getRelationAttributeValue('org', 'name');
            },
            'visible' => !\backend\modules\auth\Session::isOrganization(),
        ],
        [
            'attribute' => 'recipient_email',

        ],

        [
            'attribute' => 'sender_email',
        ],
        [
            'attribute' => 'subject',
        ],
        [
            'attribute' => 'message',
            'value' => function (EmailOutbox $model) {
                return \Illuminate\Support\Str::limit($model->message, 50);
            },
            'format' => 'html',
            'filter' => false,
            'visible' => false,
        ],
        [
            'attribute' => 'attachment',
            'value' => function (EmailOutbox $model) {
                return !empty($model->attachment) ? 'YES' : 'NO';
            },
            'filter' => false,
        ],
        [
            'attribute' => 'status',
            'value' => function (EmailOutbox $model) {
                return $model::decodeStatus($model->status);
            },
            'filter' => EmailOutbox::statusOptions(),
        ],
        [
            'attribute' => 'attempts',
            'filter' => false,
        ],
        [
            'attribute' => 'date_sent',
            'value' => function (EmailOutbox $model) {
                return \common\helpers\DateUtils::formatToLocalDate($model->date_sent);
            },
            'filter' => false,
        ],
        [
            'class' => common\widgets\grid\ActionColumn::class,
            'template' => '{view}{delete}{resend}',
            'buttons' => [
                'resend' => function ($url,EmailOutbox $model, $key) {
                    return \yii\helpers\Html::a('Resend', '#', [
                        'class' => 'grid-update',
                        'data-href' => $url,
                        'data-data-type'=>'json',
                        'data-grid'=>$model->getPjaxWidgetId(),
                        'data-confirm-message'=>\common\helpers\Lang::t('Do you want to resend this Email?')
                    ]);
                    }
            ],
            'viewOptions' => [
                'label' => '<i class="fa fa-eye"></i>',
                'data-pjax' => 0,
                'class' => 'show_modal_form',
            ],
        ],
    ],
]);
?>