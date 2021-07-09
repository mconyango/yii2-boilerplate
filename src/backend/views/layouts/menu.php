<?php

use backend\modules\auth\Session;
use common\helpers\Lang;

/* @var $this yii\web\View */
/* @var $controller \backend\controllers\BackendController */
$controller = Yii::$app->controller;

$subscription = null;
$organization = null;
if (Session::isOrganization()) {
    $organization = \backend\modules\core\models\Organization::loadModel(Session::accountId());
    $subscription = \backend\modules\subscription\models\OrgSubscription::getActiveSubscription(Session::accountId());
}
?>
<!-- Left panel : Navigation area -->
<!-- Note: This width of the aside area can be adjusted through LESS variables -->
<aside id="left-panel">
    <!-- NAVIGATION : This navigation is also responsive

    To make this navigation dynamic please make sure to link the node
    (the reference to the nav > ul) after page load. Or the navigation
    will not initialize.
    -->
    <nav>
        <!-- NOTE: Notice the gaps after each icon usage <i></i>..
        Please note that these links work a bit different than
        traditional hre="" links. See documentation for details.
    -->
        <ul>
            <li class="<?= $controller->activeMenu === 1 ? 'active' : '' ?>">
                <a href="<?= Yii::$app->homeUrl ?>"><i class="fa fa-lg fa-fw fa-dashboard"></i>
                    <span class="menu-item-parent"><?= Lang::t('DASHBOARD') ?></span></a>
            </li>

            <?php if (Session::isOrganization()): ?>

                <?php if ($subscription !== null && $subscription->canAccessModule('core')): ?>
                    <?= $this->render('@app/modules/core/views/layouts/menu') ?>
                <?php endif; ?>
                <?php if ($subscription !== null && $subscription->canAccessModule('saving')): ?>
                    <?= $this->render('@app/modules/saving/views/layouts/menu') ?>
                <?php endif; ?>
                <?php if ($subscription !== null && $subscription->canAccessModule('loan')): ?>
                    <?= $this->render('@app/modules/loan/views/layouts/menu') ?>
                <?php endif; ?>
                <?php if ($subscription !== null && $subscription->canAccessModule('accounting')): ?>
                    <?= $this->render('@app/modules/accounting/views/layouts/menu') ?>
                <?php endif; ?>
                <?php if ($subscription !== null && $subscription->canAccessModule('product')): ?>
                    <?= $this->render('@app/modules/product/views/layouts/menu') ?>
                <?php endif; ?>
                <?php if ($subscription !== null && $subscription->canAccessModule('reports')): ?>
                    <?= $this->render('@app/modules/reports/views/layouts/menu') ?>
                <?php endif; ?>

                <?= $this->render('@app/modules/conf/views/layouts/menu', [
                    'orgModel' => $organization,
                    'subscription' => $subscription
                ]) ?>

            <?php else: ?>
                <?= $this->render('@app/modules/core/views/layouts/menu') ?>
                <?= $this->render('@app/modules/subscription/views/layouts/menu') ?>
                <?= $this->render('@app/modules/reports/views/layouts/menu') ?>
                <?= $this->render('@app/modules/conf/views/layouts/menu') ?>
            <?php endif; ?>

        </ul>
    </nav>
    <span class="minifyme" data-action="minifyMenu">
        <i class="fa fa-arrow-circle-left hit"></i>
    </span>
</aside>
<!-- END NAVIGATION -->