<?php

use backend\modules\auth\Acl;
use backend\modules\auth\models\Users;
use backend\modules\conf\models\NotifTypes;
use common\helpers\Lang;
use yii\helpers\Url;
use backend\modules\auth\Session;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */
$can_update = Users::checkPrivilege(Acl::ACTION_UPDATE, $model->level_id);
$notificationsExist = NotifTypes::exists(['is_active' => 1]);
?>


<div class="list-group my-list-group">
    <a class="list-group-item disabled" href="#">
        <?= Lang::t('Account Actions') ?>
    </a>
    <a class="list-group-item" href="<?= Url::to(['/auth/profile/update']) ?>">
        <?= Lang::t('My Profile') ?>
    </a>
    <a class="list-group-item" href="<?= Url::to(['/auth/profile/change-password']) ?>">
        <?= Lang::t('Change your password') ?>
    </a>
    <?php if ($notificationsExist): ?>
        <a class="list-group-item" href="<?= Url::to(['/auth/profile/notification']) ?>">
            <?= Lang::t('My Notification Settings') ?>
        </a>
    <?php endif; ?>
    <?php if (Session::isTrader()): ?>
        <?php if (Yii::$app->user->canUpdate(\backend\modules\trader\Constants::RES_TRADER)): ?>
            <a class="list-group-item" href="<?= Url::to(['/trader/trader/update', 'id' => Session::accountId()]) ?>">
                <?= Lang::t('Company details') ?>
            </a>
            <?php if ($notificationsExist): ?>
                <a class="list-group-item" href="<?= Url::to(['/trader/settings/notification']) ?>">
                    <?= Lang::t('Company Notification Settings') ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (Yii::$app->user->canView(\backend\modules\auth\Constants::RES_USER)): ?>
            <a class="list-group-item" href="<?= Url::to(['/auth/user/index']) ?>">
                <?= Lang::t('User Accounts') ?>
            </a>
        <?php endif; ?>
        <?php if (Yii::$app->user->canView(\backend\modules\trader\Constants::RES_CUSTOMS_BOND)): ?>
            <a class="list-group-item" href="<?= Url::to(['/trader/customs-bond/index']) ?>">
                <?= Lang::t('Customs Bonds') ?>
            </a>
        <?php endif; ?>
        <?php if (Yii::$app->user->canView(\backend\modules\trader\Constants::RES_CONSIGNEE)): ?>
            <a class="list-group-item" href="<?= Url::to(['/trader/consignee/index']) ?>">
                <?= Lang::t('Customers') ?>
            </a>
        <?php endif; ?>
        <?php if (Yii::$app->user->canView(\backend\modules\trader\Constants::RES_TRANSPORTER)): ?>
            <a class="list-group-item" href="<?= Url::to(['/trader/transporter/index']) ?>">
                <?= Lang::t('Transporters') ?>
            </a>
        <?php endif; ?>
        <?php if (Yii::$app->user->canView(\backend\modules\trader\Constants::RES_TRANSPORTER)): ?>
            <a class="list-group-item" href="<?= Url::to(['/trader/vehicle/index']) ?>">
                <?= Lang::t('Trucks') ?>
            </a>
        <?php endif; ?>
        <?php if (Yii::$app->user->canView(\backend\modules\trader\Constants::RES_SUPPLIER)): ?>
            <a class="list-group-item" href="<?= Url::to(['/trader/supplier/index']) ?>">
                <?= Lang::t('Suppliers') ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>