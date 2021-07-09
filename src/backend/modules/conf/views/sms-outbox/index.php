<?php

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\conf\models\SmsOutbox */

$this->title = 'SMS Outbox';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@confModule/views/layouts/submenu'); ?>
    </div>
    <div class="col-md-10">

        <!--flash message for SMS resent successfully -->
        <?php
        if(Yii::$app->session->hasFlash('sms-resent'))
        {
            echo Yii::$app->session->getFlash('sms-resent');
        }
        ?>

        <!-- Nav-tab for sms-outbox template -->
        <?= $this->render('@confModule/views/sms-template/_tab'); ?>
        <div class="tab-content padding-top-10">
            <?= $this->render('_grid', ['model' => $searchModel]) ?>
        </div>
    </div>
</div>