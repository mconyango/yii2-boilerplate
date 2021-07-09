<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
/* @var $this \yii\web\View */
/* @var $content string */
\backend\assets\ModulesAsset::register($this);


if ($exception instanceof \yii\web\HttpException) {
    $code = $exception->statusCode;
} else {
    $code = $exception->getCode();
}
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head(); ?>
    </head>
    <body style="background: #E9E9E9;">
    <?php $this->beginBody() ?>
    <div class="main-container">
        <div id="error-unit">
            <i class="fa fa-frown-o"></i>

            <h1><?= Html::encode($name) ?></h1>

            <h3 class="uk-animation-slide-right">
                <?php if ($code == 404): ?>
                    The page you have requested does not exist. Check the address you have typed in the browser.
                <?php elseif ($code == 403): ?>
                    You are not allowed to access this page.
                <?php elseif ($code == 400): ?>
                    <?= Html::encode($message); ?>
                <?php else: ?>
                    Oops,something wrong happened. Our engineers will fix this as soon as possible.
                <?php endif; ?>
                <br/><br/><a href="<?= Yii::$app->homeUrl ?>">Go Back Home</a>
            </h3>
            <?php if (YII_DEBUG): ?>
                <code>
                    <pre><?= nl2br(Html::encode($exception->getTraceAsString())) ?></pre>
                </code>
            <?php endif; ?>
        </div>
    </div>
    <!--/.main-container-->
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>