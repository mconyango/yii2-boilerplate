<?php

use backend\modules\conf\Constants;
use common\helpers\Lang;
use common\helpers\Url;

/* @var $this yii\web\View */
/* @var $controller \backend\controllers\BackendController */
$controller = Yii::$app->controller;
?>
<div class="list-group my-list-group">
    <a href="#" class="list-group-item disabled">
        <i class="fa fa-list"></i> <?= Lang::t('System Settings') ?>
    </a>
    <a href="<?= Url::to(['/conf/settings/index']) ?>" class="list-group-item">
        <?= Lang::t('General Settings') ?>
    </a>

    <a href="<?= Url::to(['/conf/email/index']) ?>"
       class="list-group-item <?= ($controller->activeSubMenu === Constants::SUBMENU_EMAIL) ? ' active' : '' ?>">
        <?= Lang::t('Manage Email Settings') ?>
    </a>
    <a href="<?= Url::to(['/conf/notif/index']) ?>" class="list-group-item">
        <?= Lang::t('Manage Notifications Settings') ?>
    </a>
    <a href="<?= Url::to(['/conf/security-settings/password']) ?>"
       class="list-group-item <?= ($controller->activeSubMenu === Constants::SUBMENU_SECURITY) ? ' active' : '' ?>">
        <?= Lang::t('Security Settings') ?>
    </a>
</div>
