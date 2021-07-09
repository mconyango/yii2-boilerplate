<?php

use common\helpers\Lang;
use yii\helpers\Url;

?>
<ul class="nav nav-tabs my-nav">
    <li>
        <a href="<?= Url::to(['password']) ?>"><?= Lang::t('Passwords Settings') ?></a>
    </li>
</ul>