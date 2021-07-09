<?php

use backend\modules\conf\models\NumberingFormat;
use common\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $model NumberingFormat */
?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
    'createButton' => ['visible' => \backend\modules\auth\Session::isDev(), 'modal' => true],
    'showExportButton' => false,
    'columns' => [
        [
            'attribute' => 'code',
            'filter' => false,
        ],
        [
            'attribute' => 'name',
            'filter' => false,
        ],
        [
            'attribute' => 'next_number',
            'filter' => false,
        ],
        [
            'attribute' => 'min_digits',
            'filter' => false,
        ],
        [
            'attribute' => 'prefix',
            'filter' => false,
        ],
        [
            'attribute' => 'suffix',
            'filter' => false,
        ],
        [
            'attribute' => 'preview',
            'filter' => false,
        ],
        [
            'attribute' => 'is_active',
            'value' => function (NumberingFormat $model) {
                return \common\helpers\Utils::decodeBoolean($model->is_active);
            },
            'filter' => \common\helpers\Utils::booleanOptions(),
        ],
        [
            'class' => common\widgets\grid\ActionColumn::class,
            'template' => '{update}',
        ],
    ],
]);
?>