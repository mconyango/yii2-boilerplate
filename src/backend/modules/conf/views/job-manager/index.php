<?php

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\conf\models\Jobs */

$this->title = 'Job Manager';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@app/modules/conf/views/layouts/devSubmenu'); ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_grid', ['model' => $searchModel]) ?>
    </div>
</div>