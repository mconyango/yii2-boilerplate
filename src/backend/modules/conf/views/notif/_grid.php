<?php
/* @var $this yii\web\View */

/* @var $model \backend\modules\conf\models\NotifTypes */

use backend\modules\auth\Session;
use common\widgets\grid\GridView;
use common\helpers\Utils;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => Session::isDev(), 'modal' => false],
    'rowOptions' => function ($model) {
        return ["class" => "linkable", "data-href" => Url::to(['update', "id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'template_id',
            'visible' => Session::isDev(),
            'filter' => false,
        ],
        [
            'attribute' => 'name',
            'filter' => false,
        ],
        [
            'attribute' => 'description',
            'filter' => false,
        ],
        [
            'attribute' => 'is_active',
            'filter' => Utils::booleanOptions(),
            'value' => function ($model) {
                return Utils::decodeBoolean($model->is_active);
            },
        ],
        [
            'class' => \common\widgets\grid\ActionColumn::class,
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