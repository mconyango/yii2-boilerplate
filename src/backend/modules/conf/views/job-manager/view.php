<?php

/* @var $this yii\web\View */
use common\helpers\Lang;

/* @var $searchModel backend\modules\conf\models\JobProcesses */
/* @var $job backend\modules\conf\models\Jobs */

$this->title = $job->id;
$this->params['breadcrumbs'] = [
    ['label' => Lang::t('Jobs'), 'url' => ['index']],
    $this->title
];
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/conf/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_processGrid', ['model' => $searchModel, 'job' => $job]) ?>
    </div>
</div>