<?php
use backend\modules\conf\models\EmailTemplate;
use common\widgets\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model EmailTemplate*/
?>
<?= GridView::widget([
    'searchModel' => $model,
    'createButton' => ['visible' => \backend\modules\auth\Session::isDev(), 'modal' => false],
    'rowOptions' => function (EmailTemplate $model) {
        return ["class" => "linkable", "data-href" => Url::to(['update', "id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'template_id',
            'visible' => \backend\modules\auth\Session::isDev(),
        ],
        [
            'attribute' => 'org_id',
            'value' => function (EmailTemplate $model) {
                return $model->getRelationAttributeValue('org', 'name');
            },
            // 'visible' => !\backend\modules\auth\Session::isOrganization(),
        ],
        [
            'attribute' => 'name',
            'filter' => true,
        ],
        [
            'attribute' => 'subject',
            'filter' => true,
        ],
        [
            'attribute' => 'sender',
            'filter' => true,
        ],
        [
            'class' => common\widgets\grid\ActionColumn::class,
            'template' => '{update}{delete}',
            'visibleButtons' => [
                'delete' => function () {
                    return \backend\modules\auth\Session::isDev();
                },
            ],
            'buttons' => [
                'update' => function ($url) {
                    return Yii::$app->user->canUpdate() ? Html::a('<i class="fa fa-pencil text-success"></i>', $url, ['data-pjax' => 0]) : '';
                },
            ]
        ],
    ],
]);
?>