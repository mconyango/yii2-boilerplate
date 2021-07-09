<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\UserLevels */

$this->title = 'Create User Levels';
$this->params['breadcrumbs'][] = ['label' => 'User Levels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-levels-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
