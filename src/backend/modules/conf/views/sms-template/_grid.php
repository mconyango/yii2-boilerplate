<?php

use backend\modules\conf\models\SmsTemplate;
use common\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $filterOptions array */
/* @var $model SmsTemplate */
?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => \backend\modules\auth\Session::isDev(), 'modal' => true],
    'showExportButton' => false,
    'columns' => [
        [
            'attribute' => 'org_id',
            'value' => function (SmsTemplate $model) {
                return $model->getRelationAttributeValue('org', 'name');
            },
            //'visible' => !\backend\modules\auth\Session::isOrganization(),
        ],
        [
            'attribute' => 'code',
            'visible' => \backend\modules\auth\Session::isDev(),
        ],
        [
            'attribute' => 'name',
        ],
        [
            'attribute' => 'template',
            'value' => function (SmsTemplate $model) {
                return \Illuminate\Support\Str::limit($model->template);
            }
        ],
        [
            'class' => common\widgets\grid\ActionColumn::class,
            'template' => '{update}',
        ],
    ],
]);
?>