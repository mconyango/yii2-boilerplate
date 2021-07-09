<?php

use common\helpers\Lang;

/* @var $this yii\web\View */
/* @var $model backend\modules\auth\models\Users */

$this->title = Lang::t('Create User');
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
