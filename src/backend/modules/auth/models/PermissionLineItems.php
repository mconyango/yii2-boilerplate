<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-06 14:05
 * Time: 14:05
 */

namespace backend\modules\auth\models;


use common\helpers\DbUtils;
use common\widgets\lineItem\LineItem;
use common\widgets\lineItem\LineItemModelInterface;
use common\widgets\lineItem\LineItemTrait;
use yii\bootstrap\Html;

class PermissionLineItems extends Permission implements LineItemModelInterface
{
    use LineItemTrait;

    /**
     *  {@inheritdoc}
     */
    public function lineItemFields()
    {
        return [
            [
                'attribute' => 'resource_id',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_STATIC,
                'value' => function (PermissionLineItems $model) {
                    return '<span>' . Html::encode($model->getRelationAttributeValue('resource', 'name')) . '</span>';
                },
                'tdOptions' => [],
                'options' => [],
            ],
            [
                'attribute' => 'can_view',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_CHECKBOX,
                'tdOptions' => ['class' => 'text-left'],
                'options' => ['class' => 'my-roles-checkbox roles-checkbox-view'],
                'input' => function (PermissionLineItems $model) {
                    return $model->resource->viewable ? true : 'N/A';
                },
            ],
            [
                'attribute' => 'can_create',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_CHECKBOX,
                'tdOptions' => ['class' => 'text-left'],
                'options' => ['class' => 'my-roles-checkbox roles-checkbox-create'],
                'input' => function (PermissionLineItems $model) {
                    return $model->resource->creatable ? true : 'N/A';
                },
            ],
            [
                'attribute' => 'can_update',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_CHECKBOX,
                'tdOptions' => ['class' => 'text-left'],
                'options' => ['class' => 'my-roles-checkbox roles-checkbox-update'],
                'input' => function (PermissionLineItems $model) {
                    return $model->resource->editable ? true : 'N/A';
                },
            ],
            [
                'attribute' => 'can_delete',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_CHECKBOX,
                'tdOptions' => ['class' => 'text-left'],
                'options' => ['class' => 'my-roles-checkbox roles-checkbox-delete'],
                'input' => function (PermissionLineItems $model) {
                    return $model->resource->deletable ? true : 'N/A';
                },
            ],
            [
                'attribute' => 'role_id',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_HIDDEN_INPUT,
                'tdOptions' => [],
                'options' => [],
            ],
            [
                'attribute' => 'resource_id',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_HIDDEN_INPUT,
                'tdOptions' => [],
                'options' => [],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function lineItemFieldsLabels()
    {
        $viewCheckBox = Html::checkbox('check-all-view', false, ['class' => 'check-all-checkbox', 'data-target-class' => 'roles-checkbox-view']);
        $createCheckBox = Html::checkbox('check-all-create', false, ['class' => 'check-all-checkbox', 'data-target-class' => 'roles-checkbox-create']);
        $updateCheckBox = Html::checkbox('check-all-update', false, ['class' => 'check-all-checkbox', 'data-target-class' => 'roles-checkbox-update']);
        $deleteCheckBox = Html::checkbox('check-all-delete', false, ['class' => 'check-all-checkbox', 'data-target-class' => 'roles-checkbox-delete']);
        return [
            ['label' => $this->getAttributeLabel('resource_id'), 'options' => []],
            [
                'label' => $viewCheckBox . '&nbsp;&nbsp;' . $this->getAttributeLabel('can_view'),
                'options' => ['class' => 'text-left']
            ],
            [
                'label' => $createCheckBox . '&nbsp;&nbsp;' . $this->getAttributeLabel('can_create'),
                'options' => ['class' => 'text-left']
            ],
            [
                'label' => $updateCheckBox . '&nbsp;&nbsp;' . $this->getAttributeLabel('can_update'),
                'options' => ['class' => 'text-left'],
            ],
            [
                'label' => $deleteCheckBox . '&nbsp;&nbsp;' . $this->getAttributeLabel('can_delete'),
                'options' => ['class' => 'text-left']
            ],
            ['label' => '&nbsp;', 'options' => []],
        ];
    }

    /**
     * @param Roles $role
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    public static function getModels($role)
    {
        $forbiddenResources = UserLevels::getForbiddenResources($role->level_id);
        $condition = '';
        $params = [];
        if (is_array($forbiddenResources) && !empty($forbiddenResources)) {
            list($condition, $params) = DbUtils::appendInCondition('id', $forbiddenResources, $condition, $params, 'NOT IN');
        }

        $models = [];
        foreach (Resources::find()->andWhere($condition, $params)->all() as $resource) {
            $model = static::findOne(['role_id' => $role->id, 'resource_id' => $resource->id]);
            if ($model === null) {
                $model = new static([
                    'role_id' => $role->id,
                    'resource_id' => $resource->id,
                ]);
            }
            $models[] = $model;
        }

        return $models;
    }
}