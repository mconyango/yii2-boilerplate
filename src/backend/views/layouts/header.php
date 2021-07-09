<?php

use common\helpers\Lang;
use yii\helpers\Html;
use yii\helpers\Url;

$subscription = null;
$user = \backend\modules\auth\models\Users::loadModel(Yii::$app->user->id);
$dashboardLogoUrl = Yii::$app->view->theme->baseUrl . '/img/dashboard-logo.png';
$style = ' ';
$alt = \backend\modules\conf\settings\SystemSettings::getAppName();
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
    </div>
    <!-- pulled right: nav area -->
    <div class="pull-right">
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