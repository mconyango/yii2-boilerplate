<?php

use common\helpers\Lang;
use common\widgets\lineItem\LineItem;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $this \yii\web\View */
/* @var $controller \backend\controllers\BackendController */
/* @var $lineItemModels \backend\modules\auth\models\PermissionLineItems[] */
/* @var $role \backend\modules\auth\models\Roles */
$controller = Yii::$app->controller;

$form = ActiveForm::begin([
    'id' => 'role-permissions-form',
    'layout' => 'horizontal',
    'enableClientValidation' => false,
    'options' => ['enctype' => 'multipart/form-data'],
    'fieldConfig' => [
        'enableError' => false,
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-md-3',
            'offset' => 'col-md-offset-3',
            'wrapper' => 'col-md-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);
?>

<?= LineItem::widget([
    'activeForm' => $form,
    'title' => Html::encode($role->name) . '<small>' . ' Review the privileges' . '</small>',
    'parentModel' => $role,
    'tableOptions' => ['class' => 'table table-striped table-condensed'],
    'parentPrimaryKeyAttribute' => 'id',
    'lineItemModels' => $lineItemModels,
    'showLineItemsOnPageLoad' => true,
    'showAddLineButton' => false,
    'primaryKeyAttribute' => 'id',
    'foreignKeyAttribute' => 'role_id',
    'showSaveButton' => false,
    'showDeleteButton' => false,
    'finishButtonLabel' => Lang::t('UPDATE'),
    'template' => $this->render('_template'),
])
?>
<?php
ActiveForm::end();
$this->registerJs("MyApp.modules.auth.roles();");
?>