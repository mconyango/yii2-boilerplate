<?php

use backend\modules\auth\models\Resources;
use common\widgets\grid\GridView;
use common\helpers\Utils;

/* @var $this yii\web\View */
/* @var $model Resources */
?>
<?= GridView::widget([
    'searchModel' => $model,
    'createButton' => ['visible' => Yii::$app->user->canCreate(), 'modal' => true],
    'toolbarButtons' => [],
    'columns' => [
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'name',
        ],
        [
            'attribute' => 'viewable',
            'value' => function (Resources $model) {
                return Utils::decodeBoolean($model->viewable);
            }
        ],
        [
            'attribute' => 'creatable',
            'value' => function (Resources $model) {
                return Utils::decodeBoolean($model->creatable);
            }
        ],
        [
            'attribute' => 'editable',
            'value' => function (Resources $model) {
                return Utils::decodeBoolean($model->editable);
            }
        ],
        [
            'attribute' => 'deletable',
            'value' => function (Resources $model) {
                return Utils::decodeBoolean($model->deletable);
            }
        ],
        [
            'class' => common\widgets\grid\ActionColumn::class,
            'template' => '{update}',
            'buttons' => [],
        ],
    ],
]);
?>