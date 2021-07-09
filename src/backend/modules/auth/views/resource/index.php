<?php

use common\helpers\Lang;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\auth\models\Resources */

$this->title = Lang::t('Resources');
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
