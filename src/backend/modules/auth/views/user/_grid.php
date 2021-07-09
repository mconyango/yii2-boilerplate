<?php

use backend\modules\auth\Session;
use common\widgets\grid\GridView;
use common\helpers\Lang;
use yii\helpers\Html;
use backend\modules\auth\models\Users;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model Users */
?>
<?= GridView::widget([
    'searchModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate(), 'modal' => false],
    'rowOptions' => function (Users $model) {
        return ["class" => "linkable", "data-href" => Url::to(['view', "id" => $model->id])];
    },
    'columns' => [
        [
            'attribute' => 'name',
            'filter' => true,
        ],
        [
            'attribute' => 'username',
            'filter' => true,
            'enableSorting' => true,
        ],
        [
            'attribute' => 'email',
            'filter' => true,
        ],
        [
            'attribute' => 'phone',
            'filter' => true,
        ],
        [
            'attribute' => 'level_id',
            'value' => function (Users $model) {
                return $model->getRelationAttributeValue('level', 'name');
            },
            'visible' => !Session::isOrganization(),
        ],
        [
            'attribute' => 'org_id',
            'value' => function (Users $model) {
                return $model->getRelationAttributeValue('org', 'name');
            },
            'visible' => !Session::isOrganization(),
        ],
        [
            'attribute' => 'role_id',
            'value' => function (Users $model) {
                return $model->getRelationAttributeValue('role', 'name');
            },
        ],
        [
            'attribute' => 'last_login',
            'value' => function (Users $model) {
                return \common\helpers\DateUtils::formatToLocalDate($model->last_login);
            },
            'filter' => false,
        ],
        [
            'attribute' => 'status',
            'filter' => Users::statusOptions(),
            'value' => function (Users $model) {
                return Users::decodeStatus($model->status);
            },
        ],
        [
            'class' => common\widgets\grid\ActionColumn::class,
            'template' => '{view}{update}',
            'buttons' => [
                'update' => function ($url, Users $model) {
                    return Yii::$app->user->canUpdate() && $model->checkPermission(false, true, false, true) ? Html::a('<i class="fa fa-pencil text-success"></i>', $url, [
                        'data-pjax' => 0,
                        'title' => Lang::t('Update'),
                    ]) : '';
                },
            ]
        ],
    ],
]);
?>