<?php

use backend\modules\auth\Acl;
use backend\modules\auth\Constants;
use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use common\helpers\Lang;
use yii\bootstrap\Html;
use yii\helpers\Url;

$level_id = isset($level_id) ? $level_id : null;
/* @var $this yii\web\View */
/* @var $controller \common\controllers\Controller */
$controller = Yii::$app->controller;
?>
<div class="list-group">
    <a href="#" class="list-group-item disabled">
        <i class="fa fa-group"></i> <?= Lang::t('User Management') ?>
    </a>

    <?php foreach (UserLevels::getData(['id', 'name'], ['is_active' => 1]) as $level): ?>
        <?php if (Users::checkPrivilege(Acl::ACTION_VIEW, $level['id'])): ?>
            <a href="<?= Url::to(['/auth/user/index', 'level_id' => $level['id']]) ?>"
               class="list-group-item<?= $level_id == $level['id'] ? ' active' : '' ?>">
                <?= Lang::t('{level}', ['level' => Html::encode($level['name'])]) ?> <span
                        class="badge"><?= number_format(Users::getCount(['level_id' => $level['id']])) ?></span>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<div class="list-group">
    <a href="#" class="list-group-item disabled">
        <i class="fa fa-group"></i> <?= Lang::t('Roles and Privileges') ?>
    </a>
    <?php if (Yii::$app->user->canView(Constants::RES_ROLE)): ?>
        <a href="<?= Url::to(['/auth/role/index']) ?>"
           class="list-group-item<?= $controller->isSubMenuActive(Constants::SUBMENU_ROLES) ? ' active' : '' ?>">
            <?= Lang::t('Manage Roles') ?>
        </a>
    <?php endif; ?>

    <?php if (\backend\modules\auth\Session::isDev()): ?>

        <?php if (Yii::$app->user->canView(Constants::RES_RESOURCE)): ?>
            <a href="<?= Url::to(['/auth/resource/index']) ?>"
               class="list-group-item <?= $controller->isSubMenuActive(Constants::SUBMENU_RESOURCES) ? ' active' : '' ?>">
                <?= Lang::t('Manage Resources') ?>
            </a>
        <?php endif; ?>

        <?php if (Yii::$app->user->canView(Constants::RES_USER_LEVEL)): ?>
            <a href="<?= Url::to(['/auth/user-level/index']) ?>"
               class="list-group-item <?= $controller->isSubMenuActive(Constants::SUBMENU_USER_LEVELS) ? ' active' : '' ?>">
                <?= Lang::t('Manage User types') ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="list-group">
    <a href="#" class="list-group-item disabled">
        <i class="fa fa-group"></i> <?= Lang::t('Audit Trail') ?>
    </a>
    <?php if (Yii::$app->user->canView(Constants::RES_USER)): ?>
        <a href="<?= Url::to(['/auth/audit-trail/index']) ?>"
           class="list-group-item<?= $controller->isSubMenuActive(Constants::SUBMENU_AUDIT_TRAIL) ? ' active' : '' ?>">
            <?= Lang::t('View Audit Trail') ?>
        </a>
    <?php endif; ?>
</div>
