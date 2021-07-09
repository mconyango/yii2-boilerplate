<?php

use backend\modules\auth\models\UserLevels;
use common\helpers\Utils;
use common\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $model UserLevels */

?>
<?= GridView::widget([
    'searchModel' => $model,
    'filterModel' => $model,
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
            'attribute' => 'is_active',
            'value' => function (UserLevels $model) {
                return Utils::decodeBoolean($model->is_active);
            },
            'filter' => Utils::booleanOptions(),
        ],
        [
            'class' => common\widgets\grid\ActionColumn::class,
            'template' => '{update}',
            'buttons' => [],
        ],
    ],
]);
?>