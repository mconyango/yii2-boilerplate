<?php

use common\helpers\Lang;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */

$this->title = Lang::t('Change your password');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-2">
        <?= $this->render('_viewOptions', ['model' => $model]) ?>
    </div>
    <div class="col-md-10">
        <?= $this->render('_changePasswordForm', ['model' => $model]) ?>
    </div>
</div>