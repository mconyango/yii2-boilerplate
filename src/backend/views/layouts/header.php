<?php

use backend\modules\auth\Session;
use backend\modules\help\Help;
use common\helpers\DateUtils;
use common\helpers\Lang;
use yii\helpers\Html;
use yii\helpers\Url;

$subscription = null;
$user = \backend\modules\auth\models\Users::loadModel(Yii::$app->user->id);
$dashboardLogoUrl = Yii::$app->view->theme->baseUrl . '/img/dashboard-logo.png';
$style = ' ';
$alt = \backend\modules\conf\settings\SystemSettings::getAppName();
if (\backend\modules\auth\Session::isOrganization()) {
    $organization = \backend\modules\core\models\Organization::loadModel(Session::accountId());
    $subscription = \backend\modules\subscription\models\OrgSubscription::getActiveSubscription(Session::accountId());
    $dashboardLogoUrl = $organization->getLogoUrl();
    $alt = Html::encode($organization->name);
    $style = 'width:70px;height:40px;margin-top:6px;border: 1px';
}
?>
<header id="header">
    <div id="logo-group">
        <!-- PLACE YOUR LOGO HERE -->
        <a href="<?= Yii::$app->homeUrl ?>">
          <span id="logo">
            <img src="<?= $dashboardLogoUrl ?>" style="<?= $style ?>"
                 alt="<?= $alt ?>">
           </span>
        </a>
        <?= $this->render('@confModule/views/notif/notif') ?>
    </div>
    <div class="pull-left hidden" style="margin-left: 20px">
        <h1 style="color: #fff">
            <?= \backend\modules\conf\settings\SystemSettings::getAppName(); ?>
        </h1>
    </div>
    <div class="pull-left" style="margin-left: 20px">
        <?php if ($subscription !== null): ?>
            <h4 style="color: #fff; margin-top: 0.8em;">
                <?= 'Subscription Plan: <a href="' . Url::to(['/dashboard/default/status']) . '"><span class="label label-info">' . $subscription->pricingPlan->name . '</span></a> - ' ?>
                <?= 'Ends in: ' . Yii::$app->formatter->asDuration(DateUtils::getDateDiff(DateUtils::getToday(), $subscription->end_date)) ?>
            </h4>
        <?php endif; ?>
    </div>
    <!-- pulled right: nav area -->
    <div class="pull-right">
        <?php if (Session::isOrganization()): ?>
            <div class="pull-left top-help-link">
                <a href="<?= Url::to(['/workflow/workflow-task/index']) ?>" id="workflow-task-notif"
                   data-workflow-notif-url="<?= Url::to(['/workflow/workflow-task/notif-count']) ?>">
                    <i class="fa-2x fa fa-check-circle top-help-link-icon" data-toggle="tooltip" data-placement="top"
                       title="Tasks Pending Authorization"></i>
                    <span class="badge hidden" id="workflow-task-count"></span>
                </a>
            </div>
        <?php endif; ?>
        <div class="pull-left top-help-link">
            <a target="_blank"
               href="<?= Url::to(['/help/help-content/read']) ?>">
                <i class="fa-2x fa fa-question-circle top-help-link-icon" data-toggle="tooltip" data-placement="top"
                   title="System help"></i>
            </a>
        </div>
        <div id="hide-menu" class="btn-header pull-right">
            <span> <a href="javascript:void(0);" data-action="toggleMenu" title="Collapse Menu"><i
                            class="fa fa-reorder"></i></a> </span>
        </div>
        <ul id="mobile-profile-img" class="header-dropdown-list hidden-xs padding-5"
            style="display: block!important;padding-right: 2px!important;padding-left: 2px!important;">
            <li class="">
                <a href="#" class="dropdown-toggle no-margin userdropdown" data-toggle="dropdown"
                   style="background:none;">
                    <span class="hidden-xs">
                        <?= Lang::t('Welcome, {name}', ['name' => Html::encode(Yii::$app->user->identity->name)]) ?>
                    </span>
                    <img class="online"
                         style="width:30px;height:30px;margin-top:4px;margin-left:2px;border-radius: 3px;border: 1px solid #797979!important;"
                         src="<?= $user->getProfileImageUrl(32) ?>"
                         alt="Me">
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu pull-right">
                    <li>
                        <a href="<?= Url::to(['/auth/user/update', 'id' => Yii::$app->user->id]) ?>"
                           class="padding-10 padding-top-0 padding-bottom-0"> <i
                                    class="fa fa-user"></i> <?= Lang::t('Profile') ?></a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="<?= Url::to(['/auth/user/change-password']) ?>"
                           class="padding-10 padding-top-0 padding-bottom-0"> <i
                                    class="fa fa-lock"></i> <?= Lang::t('Change Password') ?></a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="<?= Url::to(['/auth/auth/logout']) ?>"
                           class="padding-10 padding-top-5 padding-bottom-5"><i
                                    class="fa fa-sign-out fa-lg"></i> <?= Lang::t('Logout') ?></a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>