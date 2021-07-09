<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
\backend\assets\ModulesAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" id="extr-page">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="robots" content="noindex, nofollow">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode(!empty($this->title) ? $this->title : \backend\modules\conf\settings\SystemSettings::getAppName()) ?></title>
        <?php $this->head(); ?>
    </head>
    <body class="animated fadeInDown">
    <?php $this->beginBody() ?>
    <!-- possible classes: minified, no-right-panel, fixed-ribbon, fixed-header, fixed-width-->
    <header id="header">
        <!--<span id="logo"></span>-->
        <div id="logo-group">
        <span id="logo">
            <img src="<?= Yii::$app->view->theme->baseUrl . '/img/logo-2.png' ?>"
                 alt="<?= \backend\modules\conf\settings\SystemSettings::getAppName(); ?>">
        </span>
        </div>
        <span id="extr-page-header-space" class="hidden">
            <span class="hidden-mobile hiddex-xs">Need an account?</span>
            <a href="#" class="btn btn-warning">Create account</a>
        </span>
    </header>

    <div id="main" role="main">
        <!-- MAIN CONTENT -->
        <div id="content" class="container">
            <?= $content; ?>
        </div>
    </div>

    <div class="page-footer" style="background: #fff;">
        <div class="row" style="margin-left: -13px;margin-right: -13px">
            <div class="col-xs-12 col-sm-6">
            <span
                    class=""><?= \backend\modules\conf\settings\SystemSettings::getAppName() ?>
                | &copy;<?= date('Y'); ?></span>
            </div>
            <div class="col-xs-6 col-sm-6 text-right hidden-xs">
                <!-- end div-->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>