<?php
use common\helpers\Lang;

/* @var $this yii\web\View */
/* @var $model backend\modules\conf\models\NotifTypes */

$this->title = Lang::t('Add Notification');
$this->params['breadcrumbs'] = [
    ['label' => 'Notification', 'url' => ['index']],
    $this->title
];
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('_form', ['model' => $model]); ?>
    </div>
</div>