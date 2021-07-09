<?php

use common\helpers\Lang;
use yii\helpers\Url;

?>

<div class="list-group my-list-group">
    <a href="#" class="list-group-item disabled">
        <i class="fa fa-list"></i> <?= Lang::t('Developer Settings') ?>
    </a>

    <a href="<?= Url::to(['/conf/logs/runtime']) ?>" class="list-group-item">
        <?= Lang::t('Runtime Logs') ?>
    </a>

    <a href="<?= Url::to(['/conf/job-manager/index']) ?>" class="list-group-item">
        <?= Lang::t('Job Manager') ?>
    </a>

    <a href="<?= Url::to(['/help/help-modules/index']) ?>" class="list-group-item">
        <?= Lang::t('Update System Manual') ?>
    </a>
</div>