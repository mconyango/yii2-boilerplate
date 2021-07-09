<?php
use backend\modules\auth\Constants;
use common\helpers\Lang;
use yii\helpers\Url;

?>
<?php if (Yii::$app->user->canView(Constants::RES_USER)): ?>
    <li class="<?= Yii::$app->controller->isMenuActive(Constants::MENU_USER_MANAGEMENT) ? 'active' : '' ?>">
        <a href="<?= Url::to(['/auth/user/index']) ?>">
            <i class="fa fa-lg fa-fw fa-group"></i>
            <span class="menu-item-parent"><?= Lang::t('MANAGE USERS') ?></span>
        </a>
    </li>
<?php endif; ?>