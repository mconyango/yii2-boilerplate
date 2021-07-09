<?php

use backend\modules\conf\models\JobProcesses;
use backend\modules\conf\models\Jobs;
use common\widgets\grid\GridView;
use common\helpers\Lang;
use common\helpers\Url;
use common\helpers\Utils;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model Jobs */
?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => true, 'modal' => true],
    'striped' => false,
    'rowOptions' => function (Jobs $model) {
        return ["class" => !$model->is_active ? "bg-danger linkable" : "linkable", "data-href" => Url::to(['view', "id" => $model->id])];
    },
    'showExportButton' => false,
    'columns' => [
        [
            'attribute' => 'id',
            'filter' => false,
        ],
        [
            'attribute' => 'execution_type',
            'value' => function (Jobs $model) {
                return Jobs::decodeExecutionType($model->execution_type);
            },
            'filter' => false,
        ],
        [
            'attribute' => 'last_run',
            'value' => function (Jobs $model) {
                return \common\helpers\DateUtils::formatToLocalDate($model->last_run, "d/m/Y H:i:s UTC");
            },
            'filter' => false,
        ],
        [
            'attribute' => 'threads',
            'filter' => false,
            'format' => 'html',
            'value' => function (Jobs $model) {
                return "<span class='label label-default'>" . $model->threads . "/" . $model->max_threads . "</span>&nbsp;<span class='label label-success'> R: " . JobProcesses::getTotalRunning($model->id) . "</span>&nbsp;<span class='label label-danger'> S: " . JobProcesses::getTotalSleeping($model->id) . "</span>";
            },
            'options' => ['style' => 'min-width:180px;'],
        ],
        [
            'attribute' => 'sleep',
            'filter' => false,
        ],
        [
            'attribute' => 'is_active',
            'filter' => Utils::booleanOptions(),
            'value' => function (Jobs $model) {
                return Utils::decodeBoolean($model->is_active);
            },
        ],
        [
            'class' => common\widgets\grid\ActionColumn::class,
            'template' => '{start}{stop}{view}{update}{delete}',
            'width' => '150px;',
            'buttons' => [
                'start' => function ($url, Jobs $model) {
                    if (Yii::$app->user->canUpdate() && !$model->is_active) {
                        return Html::a('<i class="fa fa-check text-success"></i>', 'javascript:void(0);', ['title' => Lang::t('Start the process'), 'data-pjax' => 0, 'data-href' => $url, 'data-grid' => $model->getPjaxWidgetId(), 'class' => 'grid-update']);
                    } else {
                        return "";
                    }
                },
                'stop' => function ($url, Jobs $model) {
                    if (Yii::$app->user->canUpdate() && $model->is_active) {

                        return Html::a('<i class="fa fa-ban text-danger"></i>', 'javascript:void(0);', ['title' => Lang::t('Stop all processes'), 'data-pjax' => 0, 'data-href' => $url, 'data-grid' => $model->getPjaxWidgetId(), 'class' => 'grid-update']);
                    } else {
                        return "";
                    }
                },
            ]
        ],
    ],
]);
?>