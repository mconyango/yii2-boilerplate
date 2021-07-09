<?php

use backend\modules\accounting\Constants;
use common\helpers\Lang;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $controller \backend\controllers\BackendController */
$controller = Yii::$app->controller;
?>
<div class="list-group">
    <a href="#" class="list-group-item disabled">
        <i class="fa fa-list"></i> <?= Lang::t('System Settings') ?>
    </a>
    <a class="list-group-item<?= $controller->id == "settings" ? ' active' : '' ?>"
       href="<?= Url::to(['/conf/settings/index/']) ?>">
        <?= Lang::t('System Settings') ?>
    </a>
    <a class="list-group-item<?= $controller->id == "bank" ? ' active' : '' ?>"
       href="<?= Url::to(['/core/bank/index']) ?>">
        <?= Lang::t('Master Records') ?>
    </a>
    <a class="list-group-item<?= $controller->id == "user" ? ' active' : '' ?>"
       href="<?= Url::to(['/auth/user/index']) ?>">
        <?= Lang::t('User Management') ?>
    </a>
    <a class="list-group-item<?= $controller->id == "workflow" ? ' active' : '' ?>"
       href="<?= Url::to(['/workflow/workflow/index']) ?>">
        <?= Lang::t('Work Flow') ?>
    </a>
    <?php if(\backend\modules\auth\Session::isDev()): ?>
    <a class="list-group-item<?= $controller->id == "logs" ? ' active' : '' ?>"
       href="<?= Url::to(['/conf/logs/runtime']) ?>">
        <?= Lang::t('Developer Tools') ?>
    </a>
    <?php endif; ?>
</div>