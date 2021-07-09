<?php
use common\helpers\Lang;
use yii\helpers\Html;
/* @var $model \common\models\ActiveRecord*/
$model_class_name = $model->shortClassName();
?>
<fieldset>
    <legend><?= Lang::t('Define the columns') ?></legend>
    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th><?= Lang::t('Column') ?></th>
            <th><?= Lang::t('Sample Data') ?></th>
            <th><?= Lang::t('Import as') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $k => $v): ?>
            <tr>
                <td><?= 'Column ' . $k ?></td>
                <td><?= $v ?></td>
                <td><?= Html::dropDownList("{$model_class_name}[placeholder_columns][{$k}]", $model->placeholder_columns[$k], $columns, ['class' => 'placeholder']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</fieldset>