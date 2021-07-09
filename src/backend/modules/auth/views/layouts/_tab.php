<?php

use backend\modules\auth\Constants;
use backend\modules\auth\models\Users;
use common\helpers\Lang;
use yii\helpers\Url;

/* @var $orgModel \backend\modules\core\models\Organization */
?>
<ul class="nav nav-tabs my-nav">
    <?php if (Yii::$app->user->canView(Constants::RES_USER)): ?>
        <li>
            <a href="<?= Url::to(['user/index', 'org_id' => !empty($orgModel) ? $orgModel->id : null]) ?>">
                <?= Lang::t('Users/Administrators') ?>
                <span class="badge"><?= number_format(Users::getCount(!empty($orgModel) ? ['status' => Users::STATUS_ACTIVE, 'org_id' => $orgModel->id] : ['status' => Users::STATUS_ACTIVE])) ?></span>
            </a>
        </li>
    <?php endif; ?>
    <?php if (Yii::$app->user->canView(Constants::RES_ROLE)): ?>
        <li>
            <a href="<?= Url::to(['role/index', 'org_id' => !empty($orgModel) ? $orgModel->id : null]) ?>">
                <?= Lang::t('Roles & Privileges') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if (Yii::$app->user->canView(Constants::RES_AUDIT_TRAIL)): ?>
        <li>
            <a href="<?= Url::to(['audit-trail/index', 'org_id' => !empty($orgModel) ? $orgModel->id : null]) ?>">
                <?= Lang::t('Users Audit Trails') ?>
            </a>
        </li>
        <li class="hidden">
            <a href="<?= Url::to(['audit-trail/index', 'org_id' => !empty($orgModel) ? $orgModel->id : null]) ?>">
                <?= Lang::t('Login Logs') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if (\backend\modules\auth\Session::isDev()): ?>
        <li>
            <a href="<?= Url::to(['resource/index']) ?>">
                <?= Lang::t('System Resources') ?>
            </a>
        </li>
        <li>
            <a href="<?= Url::to(['user-level/index']) ?>">
                <?= Lang::t('Account Types') ?>
            </a>
        </li>
    <?php endif; ?>
</ul>