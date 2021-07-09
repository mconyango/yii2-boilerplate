<?php

use backend\modules\conf\models\NotifTypes;
use common\helpers\Lang;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */
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
</div>