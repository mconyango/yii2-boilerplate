<?php

use backend\modules\auth\models\AuditTrail;
use common\widgets\grid\GridView;
use common\helpers\DateUtils;
use yii\helpers\Html;

/* @var $model backend\modules\auth\models\AuditTrail */
?>

<?= GridView::widget([
    'searchModel' => $model,
    'createButton' => ['visible' => false],
    'toolbarButtons' => [],
    'columns' => [
        [
            'attribute' => 'user_id',
            'value' => function (AuditTrail $model) {
                return $model->getRelationAttributeValue('user', 'name');
            }
        ],
        [
            'attribute' => 'action',
            'value' => function (AuditTrail $model) {
                return AuditTrail::decodeAction($model->action);
            }
        ],
        [
            'attribute' => 'action_description',
            'value' => function (AuditTrail $model) {
                return \Illuminate\Support\Str::limit($model->action_description);
            }
        ],
        [
            'attribute' => 'ip_address',
        ],
        [
            'attribute' => 'created_at',
            'value' => function (AuditTrail $model) {
                return DateUtils::formatToLocalDate($model->created_at);
            }
        ],
        [
            'class' => \common\widgets\grid\ActionColumn::class,
            'width' => '120px',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, AuditTrail $model) {
                    return Html::a('More details <i class="fa fa-chevron-circle-right"></i>', $url, [
                        'data-pjax' => 0,
                        'style' => 'min-width:100px;',
                        'class' => 'show_modal_form',
                        'data-grid' => $model->getPjaxWidgetId(),
                    ]);
                },
            ]
        ],
    ],
]);