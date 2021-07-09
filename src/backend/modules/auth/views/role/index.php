<?php

/* @var $this yii\web\View */
/* @var $searchModel \backend\modules\auth\models\Roles */

$this->title = \common\helpers\Lang::t('Roles & Privileges');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('@authModule/views/layouts/_tab', []) ?>
        <div class="tab-content padding-top-10">
            <?= $this->render('_grid', ['model' => $searchModel]) ?>
        </div>
    </div>
</div>
