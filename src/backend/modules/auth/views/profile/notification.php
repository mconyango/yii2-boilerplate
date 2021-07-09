<?php

use common\helpers\Lang;

/* @var $this yii\web\View */
/* @var $user \backend\modules\auth\models\Users */
/* @var $lineItemModels \backend\modules\auth\models\UsersNotificationSettings[] */

$this->title = Lang::t('My notification settings');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-2">
        <?= $this->render('@authModule/views/profile/_viewOptions', ['model' => $user]) ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('widgets/notification/_widget', ['lineItemModels' => $lineItemModels, 'user' => $user]); ?>
    </div>
</div>