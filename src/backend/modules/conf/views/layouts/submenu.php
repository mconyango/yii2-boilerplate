<?php

use backend\modules\auth\Session;
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
        <a href="<?= Url::to(['/conf/sms-template/index']) ?>"
           class="list-group-item <?= ($controller->activeSubMenu === Constants::SUBMENU_SMS) ? ' active' : '' ?>">
            <?= Lang::t('Manage Sms Settings') ?>
        </a>
        <a href="<?= Url::to(['/conf/notif/index']) ?>" class="list-group-item">
            <?= Lang::t('Manage Notifications Settings') ?>
        </a>
    <a href="<?= Url::to(['/conf/security-settings/password']) ?>"
       class="list-group-item <?= ($controller->activeSubMenu === Constants::SUBMENU_SECURITY) ? ' active' : '' ?>">
        <?= Lang::t('Security Settings') ?>
    </a>
    <a href="<?= Url::to(['/conf/registration-settings/index']) ?>"
       class="list-group-item <?= ($controller->activeSubMenu === Constants::SUBMENU_REGISTRATION) ? ' active' : '' ?>">
        <?= Lang::t('Registration Settings') ?>
    </a>
    <?php if (Session::isOrganization()): ?>
        <a href="<?= Url::to(['/conf/financial-calendar-settings/index']) ?>" class="list-group-item<?= ($controller->activeSubMenu === Constants::SUBMENU_FINANCIAL_CALENDAR) ? ' active' : '' ?>">
            <?= Lang::t('Financial Calendar Settings') ?>
        </a>
    <?php endif; ?>
    <?php if (Session::isDev()): ?>
        <a href="<?= Url::to(['/reports/reports/index']) ?>" class="list-group-item hidden">
            <?= Lang::t('Manage Reports Configurations') ?>
        </a>
    <?php endif; ?>
    <?php if (!Session::isOrganization()): ?>
        <a href="<?= Url::to(['/conf/settings/google-map']) ?>" class="list-group-item">
            <?= Lang::t('Google Map Settings') ?>
        </a>
    <?php endif; ?>
    <a href="<?= Url::to(['/conf/number-format/index']) ?>" class="list-group-item">
        <?= Lang::t('Numbering formats') ?>
    </a>
</div>
