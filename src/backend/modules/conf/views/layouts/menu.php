<?php

use backend\modules\auth\Session;
use backend\modules\conf\Constants;
use common\helpers\Lang;
use yii\helpers\Url;

/* @var $subscription \backend\modules\subscription\models\OrgSubscription */
/* @var $organization \backend\modules\core\models\Organization */
?>
<li class="<?= $this->context->activeMenu === Constants::MENU_SETTINGS ? 'active' : '' ?>">
    <a href="#">
        <i class="fa fa-lg fa-fw fa-wrench"></i>
        <span class="menu-item-parent"><?= Lang::t('SETTINGS') ?></span>
    </a>
    <ul>
        <?php if (Session::isOrganization()): ?>

            <?php if ($subscription !== null && $subscription->canAccessModule('conf') && Yii::$app->user->canView(Constants::RES_SETTINGS)): ?>
                <li><a href="<?= Url::to(['/conf/settings/index']) ?>"><?= Lang::t('System Settings') ?></a></li>
            <?php endif; ?>

            <?php if ($subscription !== null && $subscription->canAccessModule('core') && Yii::$app->user->canView(Constants::RES_SETTINGS)): ?>
                <li><a href="<?= Url::to(['/core/bank/index']) ?>"><?= Lang::t('Master Records') ?></a></li>
            <?php endif; ?>

            <?php if ($subscription !== null && $subscription->canAccessModule('auth') && Yii::$app->user->canView(\backend\modules\auth\Constants::RES_USER)): ?>
                <li><a href="<?= Url::to(['/auth/user/index']) ?>"><?= Lang::t('User Management') ?></a></li>
            <?php endif; ?>

            <?php if ($subscription !== null && $subscription->canAccessModule('workflow') && Yii::$app->user->canView(\backend\modules\workflow\Constants::RES_WORKFLOW)): ?>
                <li><a href="<?= Url::to(['/workflow/workflow/index']) ?>"><?= Lang::t('Workflow') ?></a></li>
            <?php endif; ?>

        <?php else: ?>
            <li><a href="<?= Url::to(['/conf/settings/index']) ?>"><?= Lang::t('System Settings') ?></a></li>
            <li><a href="<?= Url::to(['/core/bank/index']) ?>"><?= Lang::t('Master Records') ?></a></li>
            <li><a href="<?= Url::to(['/auth/user/index']) ?>"><?= Lang::t('User Management') ?></a></li>
        <?php endif; ?>

        <?php if (Session::isDev()): ?>
            <li><a href="<?= Url::to(['/conf/logs/runtime']) ?>"><?= Lang::t('Developer tools') ?></a></li>
        <?php endif; ?>

    </ul>
</li>
