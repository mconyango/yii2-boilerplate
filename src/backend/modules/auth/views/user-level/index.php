<?php

use common\helpers\Lang;
use common\helpers\Utils;

/* @var $searchModel backend\modules\auth\models\UserLevels */
/* @var $this yii\web\View */
/* @var $controller \backend\controllers\BackendController */
$controller = Yii::$app->controller;
$this->title = Lang::t(Utils::pluralize($controller->resourceLabel));
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('@authModule/views/layouts/_tab') ?>
        <div class="tab-content padding-top-10">
            <?= $this->render('_grid', ['model' => $searchModel]) ?>
        </div>
    </div>
</div>