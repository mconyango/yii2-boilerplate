<?php

use common\helpers\Lang;

/* @var $this yii\web\View */
/* @var $controller \backend\controllers\BackendController */
$controller = Yii::$app->controller;
?>
<!-- Left panel : Navigation area -->
<!-- Note: This width of the aside area can be adjusted through LESS variables -->
<aside id="left-panel">
    <!-- NAVIGATION : This navigation is also responsive

    To make this navigation dynamic please make sure to link the node
    (the reference to the nav > ul) after page load. Or the navigation
    will not initialize.
    -->
    <nav>
        <!-- NOTE: Notice the gaps after each icon usage <i></i>..
        Please note that these links work a bit different than
        traditional hre="" links. See documentation for details.
    -->
        <ul>
            <li class="<?= $controller->activeMenu === 1 ? 'active' : '' ?>">
                <a href="<?= Yii::$app->homeUrl ?>"><i class="fa fa-lg fa-fw fa-dashboard"></i>
                    <span class="menu-item-parent"><?= Lang::t('DASHBOARD') ?></span></a>
            </li>
        </ul>
    </nav>
    <span class="minifyme" data-action="minifyMenu">
        <i class="fa fa-arrow-circle-left hit"></i>
    </span>
</aside>
<!-- END NAVIGATION -->