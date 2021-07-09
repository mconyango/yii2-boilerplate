<?php

use common\helpers\Lang;

/* @var $model backend\modules\auth\models\Users */
/* @var $this yii\web\View */

$this->title = Lang::t('Update Profile');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-xs-12 col-sm-2">
        <?= $this->render('_viewOptions', ['model' => $model]) ?>
    </div>
    <div class="col-xs-12 col-sm-10">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>