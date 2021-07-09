<?php

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $role \backend\modules\auth\models\Roles */
/* @var $lineItemModels \backend\modules\auth\models\PermissionLineItems[] */

$this->title = Html::encode($role->name);
$this->params['breadcrumbs'][] = ['label' => 'Roles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <?= $this->render('widgets/lineItems/_widget', ['lineItemModels' => $lineItemModels, 'role' => $role]); ?>
    </div>
</div>