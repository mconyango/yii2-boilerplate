<?php

$this->title = \common\helpers\Lang::t('Audit Trail');
$this->params['breadcrumbs'][] = $this->title;

/* @var $searchModel \backend\modules\auth\models\AuditTrail */
/* @var $this yii\web\View */
/* @var $filterOptions array */
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('@authModule/views/layouts/_tab', []) ?>
        <div class="tab-content padding-top-10">
            <?= $this->render('_filter', ['model' => $searchModel, 'filterOptions' => $filterOptions]); ?>
            <?= $this->render('_grid', ['model' => $searchModel, 'filterOptions' => $filterOptions]) ?>
        </div>
    </div>
</div>

