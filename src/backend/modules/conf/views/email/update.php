<?php
use common\helpers\Lang;

/* @var $this yii\web\View */
/* @var $model backend\modules\conf\models\EmailTemplate */

$this->title = Lang::t('Update Email Template');
$this->params['breadcrumbs'] = [
    ['label' => 'Email Templates', 'url' => ['index']],
    $this->title
];
?>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('_form', ['model' => $model]); ?>
    </div>
</div>