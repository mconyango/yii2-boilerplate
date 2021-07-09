<?php

use common\helpers\Lang;
use yii\helpers\Url;

?>
<ul class="nav nav-tabs my-nav">
    <li>
        <a href="<?= Url::to(['email/index']) ?>"><?= Lang::t('Email Templates') ?></a>
    </li>
    <li>
        <a href="<?= Url::to(['email/settings']) ?>"><?= Lang::t('Email Settings') ?></a>
    </li>
    <li>
        <a href="<?= Url::to(['email-outbox/index']) ?>"><?= Lang::t('Email Outbox') ?></a>
    </li>
</ul>