<?php

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */

$this->title = \common\helpers\Lang::t('Update user details');
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($model->name), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>