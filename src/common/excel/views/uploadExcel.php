<?php
/* @var $model \common\models\ActiveRecord */
use common\widgets\fineuploader\Fineuploader;
use common\helpers\Lang;
use yii\helpers\Html;
use yii\helpers\Url;

$class_name = strtolower($model->shortClassName());
$label_size = isset($label_size) ? $label_size : 2;
$input_size = isset($input_name) ? $input_name : 10;
$label_class = 'col-md-' . $label_size;
$input_class = 'col-md-' . $input_size;
$offset_class = 'col-md-offset-' . $label_size;
$model_class_name = strtolower($model->shortClassName());
?>

<div class="form-group">
    <?= Html::activeLabel($model, 'file', ['class' => $label_class . ' control-label']) ?>
    <div class="<?= $input_class ?>">
        <?= Html::activeHiddenInput($model, 'file'); ?>
        <div>
            <?= Fineuploader::widget([
                'buttonIcon' => 'fa fa-open',
                'buttonLabel' => 'Browse File (Excel or CSV)',
                'fileType' => Fineuploader::FILE_TYPE_EXCEL,
                'fileSelector' => '#' . $class_name . '-file',
                'alertSelector' => '#file-progress-notif',
                'excelSheetSelector' => '#' . $class_name . '-sheet',
                'options' => [
                    'request' => [
                        'endpoint' => Url::to(['/helper/upload-file','excel'=>true]),
                        'params' => [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken]
                    ],
                    'validation' => [
                        'allowedExtensions' => ['csv', 'xls', 'xlsx'],
                        'sizeLimit' => 30 * 1024 * 1024,
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
            <?= Html::error($model, 'file') ?>

            <div class="checkbox">
                <label>
                    <?= Html::checkbox('excel-skip-first-row',true,['id'=>'excel-skip-first-row']) ?> Skip the first row (If the first row is column names, start reading data from the 2nd row.)
                </label>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <?= Html::activeLabel($model, 'sheet', ['label' => Lang::t('Sheet:'), 'class' => $label_class . ' control-label']) ?>
    <div class="<?= $input_class ?>">
        <?= Html::activeDropDownList($model, 'sheet', [], ['class' => '']) ?>
    </div>
</div>

<div class="row">
    <div class="<?= $offset_class . ' ' . $input_class ?>">

        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne"
                           aria-expanded="true" aria-controls="collapseOne">
                            <i class="fa fa-chevron-down"></i> <?=Lang::t('Advanced Excel Options')?>:
                        </a>
                    </h4>
                </div>
                <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">
                        <fieldset>
                            <legend>Get Data From:</legend>
                            <div class="form-group">
                                <label class="<?= $label_class ?> control-label"><?= Lang::t('Row:') ?></label>

                                <div class="col-xs-2">
                                    <?= Html::activeTextInput($model, 'start_row', ['class' => '', 'placeholder' => $model->getAttributeLabel('start_row')]) ?>
                                </div>

                                <div class="col-xs-1">
                                    <label class="control-label"><?= Lang::t('To') ?></label>
                                </div>

                                <div class="col-xs-2">
                                    <?= Html::activeTextInput($model, 'end_row', ['class' => '', 'placeholder' => $model->getAttributeLabel('end_row')]) ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="<?= $label_class ?> control-label"><?= Lang::t('Column:') ?></label>

                                <div class="col-xs-2">
                                    <?= Html::activeTextInput($model, 'start_column', ['class' => '', 'placeholder' => $model->getAttributeLabel('start_column'), 'maxlength' => 1]) ?>
                                </div>

                                <div class="col-xs-1">
                                    <label class="control-label"><?= Lang::t('To') ?></label>
                                </div>

                                <div class="col-xs-2">
                                    <?= Html::activeTextInput($model, 'end_column', ['class' => '', 'placeholder' => $model->getAttributeLabel('end_column'), 'maxlength' => 1]) ?>
                                </div>
                            </div>
                        </fieldset>
                        <div id="placeholder_columns" class="form-group padding-10 hidden"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$options = [
    'form' => $form_id,
    'previewUrl' => $previewUrl,
    'excel' => [
        'sheetSelector' => '#' . $model_class_name . '-sheet',
        'startRowSelector' => '#' . $model_class_name . '-start_row',
        'endRowSelector' => '#' . $model_class_name . '-end_row',
        'startColumnSelector' => '#' . $model_class_name . '-start_column',
        'endColumnSelector' => '#' . $model_class_name . '-end_column',
        'skipFirstRowSelector'=>'#excel-skip-first-row',
    ],
];

$this->registerJs("MyApp.plugin.importExcel(" . \yii\helpers\Json::encode($options) . ");");
?>
