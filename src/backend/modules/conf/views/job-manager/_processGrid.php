<?php

use backend\modules\conf\models\JobProcesses;
use common\widgets\grid\GridView;
use common\helpers\DateUtils;
use common\helpers\Lang;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model JobProcesses */
/* @var $job \backend\modules\conf\models\Jobs */
?>
<?= GridView::widget([
    'searchModel' => $model,
    'panel' => [
        'before' => Html::tag('span', Lang::t('Running: {running}, Sleeping {sleeping}', ['running' => JobProcesses::getTotalRunning($job->id), 'sleeping' => JobProcesses::getTotalSleeping($job->id)]), ['class' => 'well well-sm well-light', 'style' => 'padding-top: 5px;padding-bottom: 8px;']),
    ],
    'createButton' => ['visible' => false],
    'refreshUrl' => \common\helpers\Url::to(['view', 'id' => $job->id]),
    'striped' => false,
    'rowOptions' => function (JobProcesses $model) {
        return ['class' => $model->status === JobProcesses::STATUS_RUNNING ? "bg-success" : "bg-danger"];
    },
    'columns' => [
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'status',
            'value' => function (JobProcesses $model) {
                return JobProcesses::decodeStatus($model->status);
            },
        ],
        [
            'attribute' => 'created_at',
            'value' => function (JobProcesses $model) {
                return DateUtils::formatToLocalDate($model->created_at, "d/m/Y H:i:s");
            },
        ],
        [
            'attribute' => 'last_run_datetime',
            'value' => function (JobProcesses $model) {
                return DateUtils::formatToLocalDate($model->last_run_datetime, "d/m/Y H:i:s");
            },
        ],
    ],
]);
?>