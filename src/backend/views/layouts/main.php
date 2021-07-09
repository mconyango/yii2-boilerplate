<?php

use backend\widgets\Alert;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $controller \backend\controllers\BackendController */
\backend\assets\ModulesAsset::register($this);
$controller = Yii::$app->controller;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= !empty($this->title) ? Html::encode($this->title) : \backend\modules\conf\settings\SystemSettings::getAppName() ?></title>
    <?php $this->head(); ?>
</head>
<body class="fixed-header menu-on-top smart-style-3 fixed-navigation">
<?php $this->beginBody() ?>
<!--HEADER SECTION-->
<?= $this->render('@app/views/layouts/header') ?>
<!--END HEADER SECTION-->
<!--MENU SECTION-->
<?php if (!(isset($controller->hideNavMenu) && $controller->hideNavMenu)): ?>
    <?= $this->render('@app/views/layouts/menu') ?>
<?php endif; ?>
<!--END MENU SECTION-->
<!--MAIN PANEL-->
<div id="main" role="main"
     style="<?= ((isset($controller->hideNavMenu) && $controller->hideNavMenu)) ? "margin-top: 0px !important;" : "" ?>">
    <!-- RIBBON -->
    <div class="row">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <div class="col-sm-10">
                <div id="ribbon">
                    <!-- breadcrumb -->
                    <?= Breadcrumbs::widget([
                        'options' => ['class' => 'breadcrumb'],
                        'itemTemplate' => "<li>{link}</li>\n", // template for all links
                        'activeItemTemplate' => "<li class=\"active\">{link}</li>\n",
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                    ]) ?>
                    <!-- end breadcrumb -->
                </div>
                <!-- END RIBBON -->
            </div>
        <?php endif; ?>
        <?php if (isset($controller->enableHelpLink) && $controller->enableHelpLink): ?>
            <div class="col-sm-2">
                <div class="pull-right context-help-link">
                    <a target="_blank" href="<?= \backend\modules\help\Help::getContentUrl($this->context) ?>">
                        <i class="fa-2x fa fa-question-circle" data-toggle="tooltip" data-placement="top"
                           title="Help Information for this module"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- MAIN CONTENT -->
    <div id="content">
        <div class="row" style="margin-left: -13px;margin-right: -13px">
            <div class="col-md-12">
                <?= Alert::widget(); ?>
                <?= $content; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->render('@app/views/layouts/footer') ?>
<!--END MAIN PANEL-->
<!--modal-->
<div class="modal fade" id="my_bs_modal" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        </div>
    </div>
</div>
<!--end modal-->
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
