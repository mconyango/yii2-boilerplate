<?php

use common\widgets\fineuploader\Fineuploader;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $model \backend\modules\auth\models\Users */
/* @var $this \yii\web\View */

$class_name = strtolower($model->shortClassName());
$fileAttribute = 'profile_image';
$tmpFileAttribute = 'tmp_' . $fileAttribute;
$notif_id = $fileAttribute . '_upload_notif';

?>
<div class="form-group">
    <?= Html::activeLabel($model, $tmpFileAttribute, ['class' => 'control-label col-md-4']) ?>
    <div class="col-md-8">
        <?= Html::activeHiddenInput($model, $tmpFileAttribute) ?>
        <div>
            <?= Fineuploader::widget([
                'buttonIcon' => 'fa fa-open',
                'buttonLabel' => 'Browse Image',
                'fileType' => Fineuploader::FILE_TYPE_IMAGE,
                'fileSelector' => '#' . Html::getInputId($model, $tmpFileAttribute),
                'alertSelector' => '#' . $notif_id,
                'options' => [
                    'request' => [
                        'endpoint' => Url::to(['/helper/upload-file']),
                        'params' => [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken]
                    ],
                    'validation' => [
                        'allowedExtensions' => ['jpeg', 'jpg', 'png'],
                        'sizeLimit' => 5 * 1024 * 1024,
                    ],
                    'deleteFile' => [
                        'enabled' => true,
                        'method' => 'POST',
                        'endpoint' => Url::to(['/helper/delete-upload']),
                        'params' => [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken],
                    ],
                    'classes' => [
                        'success' => 'alert alert-success',
                        'fail' => 'alert alert-error'
                    ],
                    'multiple' => false,
                    'debug' => false,
                ]
            ]) ?>
            <div id="<?= $notif_id ?>"></div>
        </div>
    </div>
</div>