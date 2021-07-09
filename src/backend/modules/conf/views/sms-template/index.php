<?php

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\conf\models\SmsTemplate */

$this->title = 'Manage SMS Templates';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@confModule/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_tab'); ?>
        <div class="tab-content padding-top-10">
            <?= $this->render('_grid', ['model' => $searchModel]) ?>
        </div>
    </div>
</div>