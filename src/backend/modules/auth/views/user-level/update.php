<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\UserLevels */

$this->title = 'Update User Levels: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'User Levels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-levels-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
