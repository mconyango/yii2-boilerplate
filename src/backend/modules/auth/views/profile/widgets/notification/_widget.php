<?php

use common\helpers\Lang;
use common\widgets\lineItem\LineItem;
use yii\bootstrap\ActiveForm;

/* @var $this \yii\web\View */
/* @var $controller \backend\controllers\BackendController */
/* @var $lineItemModels \backend\modules\auth\models\UsersNotificationSettings[] */
/* @var $user \backend\modules\auth\models\Users */
$controller = Yii::$app->controller;

$form = ActiveForm::begin([
    'id' => 'trader-notif-settings-form',
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
    'title' => Lang::t('Notification settings') . '<small>' . ' only applicable if the your account has been configured to receive the notification' . '</small>',
    'parentModel' => $user,
    'tableOptions' => ['class' => 'table table-striped table-condensed'],
    'parentPrimaryKeyAttribute' => 'id',
    'lineItemModels' => $lineItemModels,
    'showLineItemsOnPageLoad' => true,
    'showAddLineButton' => false,
    'primaryKeyAttribute' => 'id',
    'foreignKeyAttribute' => 'user_id',
    'showSaveButton' => false,
    'showDeleteButton' => false,
    'finishButtonLabel' => Lang::t('UPDATE'),
    'template' => $this->render('_template'),
])
?>
<?php
ActiveForm::end();
?>

