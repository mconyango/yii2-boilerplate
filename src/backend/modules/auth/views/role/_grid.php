<?php

use backend\modules\auth\models\Roles;
use backend\modules\auth\Session;
use common\helpers\Utils;
use common\widgets\grid\GridView;
use common\helpers\Lang;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model Roles */

?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate(), 'modal' => true],
    'toolbarButtons' => [],
    'rowOptions' => function (Roles $model) {
        return ["class" => "linkable", "data-href" => Url::to(['view', "id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'name',
            'filter' => false,
        ],
        [
            'attribute' => 'description',
            'filter' => false,
            'visible' => false,
        ],
        [
            'attribute' => 'level_id',
            'value' => function (Roles $model) {
                return \backend\modules\auth\models\UserLevels::getFieldByPk($model->level_id, 'name');
            },
            'filter' => false,
            'visible' => Session::isDev(),
        ],
        [
            'attribute' => 'is_active',
            'value' => function (Roles $model) {
                return Utils::decodeBoolean($model->is_active);
            },
            'filter' => Utils::booleanOptions(),
        ],
        [
            'class' => common\widgets\grid\ActionColumn::class,
            'template' => '{view}{update}',
            'viewOptions' => ['label' => 'Update Privileges'],
            'width' => '150px',
            'buttons' => [
                'update' => function ($url, Roles $model) {
                    return Yii::$app->user->canUpdate() && !Session::isOrganization() ? Html::a('<i class="fa fa-pencil text-success"></i>', $url, [
                        'data-pjax' => 0,
                        'class' => 'show_modal_form',
                        'data-grid' => $model->getPjaxWidgetId(),
                        'title' => Lang::t('Update'),
                    ]) : '';
                },
            ]
        ],
    ],
]);
?>