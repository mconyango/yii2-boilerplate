<?php

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\auth\models\Users */
/* @var $orgModel \backend\modules\core\models\Organization */

$this->title = \common\helpers\Lang::t('Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('@authModule/views/layouts/_tab', ['orgModel' => $orgModel]) ?>
        <div class="tab-content padding-top-10">
            <?= $this->render('_filter', ['model' => $searchModel, 'filterOptions' => $filterOptions]) ?>
            <hr/>
            <?= $this->render('_grid', ['model' => $searchModel, 'filterOptions' => $filterOptions]) ?>
        </div>
    </div>
</div>
