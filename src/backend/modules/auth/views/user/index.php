<?php

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\auth\models\Users */
/* @var $filterOptions array */

$this->title = \common\helpers\Lang::t('Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('@authModule/views/layouts/_tab', []) ?>
        <div class="tab-content padding-top-10">
            <?= $this->render('_filter', ['model' => $searchModel, 'filterOptions' => $filterOptions]) ?>
            <hr/>
            <?= $this->render('_grid', ['model' => $searchModel, 'filterOptions' => $filterOptions]) ?>
        </div>
    </div>
</div>
