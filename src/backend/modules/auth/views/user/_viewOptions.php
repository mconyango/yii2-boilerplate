<?php

use backend\modules\auth\Constants;
use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use common\helpers\Lang;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */

$can_update = Yii::$app->user->canUpdate(Constants::RES_USER) && $model->checkPermission(false, false, false, false);
?>
<div class="text-center">
    <a href="<?= Url::to(['user/view', 'id' => $model->id]) ?>">
        <img id="avator" class="img-responsive thumbnail img-center"
             src="<?= $model->getProfileImageUrl(256) ?>">
    </a>
    <ul class="list-unstyled">
        <li>
            <small>
                <?= Lang::t('Account Type') ?>:
                <strong><?= UserLevels::getFieldByPk($model->level_id, 'name'); ?></strong>
            </small>
        </li>
        <li>
            <small>
                <?= Lang::t('Account status') ?>:
                <span class="<?= $model->status === Users::STATUS_ACTIVE ? 'text-success' : 'text-danger' ?>">
                <strong><?= Users::decodeStatus($model->status); ?></strong>
            </span>
            </small>
        </li>
    </ul>
</div>

<div class="list-group">
    <?php if ($can_update): ?>
        <a class="list-group-item"
           href="<?= Url::to(['/auth/user/update', 'id' => $model->id]) ?>">
            <i class="fa fa-pencil text-success"></i> <?= Lang::t('Update details') ?>
        </a>

        <?php if ($model->status === Users::STATUS_ACTIVE): ?>
            <a class="list-group-item simple-ajax-post" href="javascript:void(0);"
               data-href="<?= Url::to(['/auth/user/change-status', 'id' => $model->id, 'status' => Users::STATUS_BLOCKED]) ?>"
               data-confirm-message="<?= Lang::t('GENERIC_CONFIRM') ?>" data-refresh="1">
                <i class="fa fa-ban text-danger"></i> <?= Lang::t('Block Account') ?>
            </a>
        <?php else: ?>
            <a class="list-group-item simple-ajax-post" href="javascript:void(0);"
               data-href="<?= Url::to(['/auth/user/change-status', 'id' => $model->id, 'status' => Users::STATUS_ACTIVE]) ?>"
               data-confirm-message="<?= Lang::t('GENERIC_CONFIRM') ?>" data-refresh="1">
                <i class="fa fa-check-circle text-success"></i> <?= Lang::t('Activate Account') ?>
            </a>
        <?php endif; ?>

        <a class="list-group-item" href="<?= Url::to(['/auth/user/reset-password', 'id' => $model->id]) ?>">
            <i class="fa fa-refresh text-primary"></i> <?= Lang::t('Reset password') ?>
        </a>
    <?php endif; ?>

    <?php if ($model->isMyAccount()): ?>
        <a class="list-group-item" href="<?= Url::to(['/auth/user/change-password']) ?>">
            <i class="fa fa-lock text-success"></i> <?= Lang::t('Change your password') ?>
        </a>
    <?php endif; ?>
</div>