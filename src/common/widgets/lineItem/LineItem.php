<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-03-03 3:23 PM
 */

namespace common\widgets\lineItem;


use common\helpers\Lang;
use common\helpers\Url;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use kartik\number\NumberControl;
use Yii;
use yii\base\Widget;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\JsExpression;

class LineItem extends Widget
{
    const ACTION_PARAM_APPEND_ITEM = 'lineItem_append_item';
    const ACTION_PARAM_SAVE_ITEM = 'lineItem_save_item';
    const ACTION_PARAM_NEXT_ITEM_INDEX = 'lineItem_next_index';
    const ACTION_PARAM_DELETE_ITEM = 'lineItem_delete_item';
    const ACTION_PARAM_ITEM_ID = 'lineItem_id';
    const LINE_ITEM_FIELD_TYPE_TEXT_INPUT = 1;
    const LINE_ITEM_FIELD_TYPE_TEXTAREA = 2;
    const LINE_ITEM_FIELD_TYPE_DROP_DOWN_LIST = 3;
    const LINE_ITEM_FIELD_TYPE_CHECKBOX = 4;
    const LINE_ITEM_FIELD_TYPE_HIDDEN_INPUT = 5;
    const LINE_ITEM_FIELD_TYPE_STATIC = 6;
    const LINE_ITEM_FIELD_TYPE_NUMBER = 7;
    const LINE_ITEM_FIELD_TYPE_FILE = 8;

    /**
     * @var ActiveForm
     */
    public $activeForm;
    /**
     * @var string
     */
    public $title;

    /**
     * @var ActiveRecord
     */
    public $parentModel;
    /**
     * @var string
     */
    public $parentPrimaryKeyAttribute = 'id';
    /**
     * @var ActiveRecord[]|LineItemModelInterface[]
     */
    public $lineItemModels = [];
    /**
     * Widget template
     * @var string
     */
    public $template = '{items}<br/>{finishButton}';
    /**
     * @var string
     */
    public $beforeLineItemsTemplate = <<< HTML
    {title}
HTML;

    /**
     * @var string
     */
    /*public $beforeLineItemsTemplate = <<< HTML
    {title} <span class="pull-right">{lineItemsCount} Items</span>
HTML;*/
    /**
     * @var string
     */
    public $primaryKeyAttribute = 'id';
    /**
     * @var string
     */
    public $foreignKeyAttribute;
    /**
     * LineItems Table Html Options
     * @var array
     */
    public $tableOptions = ['class' => 'table'];
    /**
     * <thead> html options
     * @var array
     */
    public $theadOptions = [];
    /**
     * <thead><tr {{options}}>/<tr></thead>
     * @var array
     */
    public $theadTrOptions = [];
    /**
     * tbody html options
     * @var array
     */
    public $tbodyOptions = [];
    /**
     * <tbody><tr {{options}}></tr></tbody
     * @var array
     */
    public $tbodyTrOptions = ['class' => 'line-item'];
    /**
     * @var int
     */
    public $nextItemIndex = 1;
    /**
     * @var
     */
    public $lineItemActionButtonsTemplate = '{save_button}&nbsp;&nbsp;&nbsp;&nbsp;{delete_button}';
    /**
     *
     * @var array
     */
    public $lineItemActionButtonsWrapperOptions = ['style' => 'min-width: 20px;text-align: center;'];
    /**
     *
     * @var array
     */
    public $saveButtonOptions = [];
    /**
     *
     * @var array
     */
    public $deleteButtonOptions = ['class' => 'text-muted'];
    /**
     *
     * @var string
     */
    public $saveButtonLabel = '<i class="fa fa-check-circle fa-2x"></i>';
    /**
     *
     * @var string
     */
    public $deleteButtonLabel = '<i class="fa fa-trash fa-2x"></i>';
    /**
     *
     * @var bool
     */
    public $showSaveButton = true;
    /**
     *
     * @var bool
     */
    public $showDeleteButton = true;
    /**
     *
     * @var bool
     */
    public $showPanel = true;
    /**
     * @var array
     */
    public $panelOptions = ['class' => 'panel panel-default'];
    /**
     * @var array
     */
    public $panelHeadingOptions = ['class' => 'panel-heading'];
    /**
     * @var array
     */
    public $panelTitleOptions = ['class' => 'panel-title'];
    /**
     * @var array
     */
    public $panelFooterOptions = ['class' => 'panel-footer clearfix text-right'];
    /**
     * @var array|string
     */
    public $addLineItemUrl;
    /**
     *
     * @var string
     */
    public $saveLineItemUrl;
    /**
     *
     * @var string
     */
    public $deleteLineItemUrl;
    /**
     * Array e.g ["class"=>'add-item-link',"label"=>'Add New Item']
     * @var array
     */
    public $addLineItemButtonOptions = ['class' => 'btn btn-default btn-sm'];
    /**
     *
     * @var string
     */
    public $addLineItemLabel;
    /**
     * @var bool
     */
    public $showAddLineButton = true;
    /**
     *
     * @var string
     */
    public $beforeFinish;
    /**
     * @var bool
     */
    public $showFinishButton = false;
    /**
     * @var array
     */
    public $finishButtonOptions = ['class' => 'btn btn-primary'];
    /**
     * @var string
     */
    public $finishButtonLabel = 'SAVE &amp; CONTINUE';
    /**
     *
     * @var string
     */
    public $afterFinish;
    /**
     * A js code that fires before saving a lineItem
     * @var string
     */
    public $beforeSave;
    /**
     * A js code that fires after saving a lineItem
     * @var string
     */
    public $afterSave;
    /**
     *A js code that fires before deleting a lineItem
     * @var string
     */
    public $beforeDelete;
    /**
     *A js code that fires after deleting a lineItem
     * @var string
     */
    public $afterDelete;
    /**
     * @var string
     */
    public $afterAdd;
    /**
     *
     * @var string
     */
    private $saveButtonCssClass = 'save-line-item';
    /**
     *
     * @var string
     */
    private $deleteButtonCssClass = 'delete-line-item';
    /**
     * @var array
     */
    private $_lineItemFields;

    //input constants
    /**
     * @var array
     */
    private $_lineItemFieldsLabels;
    private $_primaryKeyFieldSelector;
    private $_foreignKeyFieldSelector;
    private $_parentPrimaryKeyFieldSelector;
    /**
     * @var bool
     */
    private $_actionAppendLine = false;
    /**
     * @var null
     */
    private $_itemTrIdPrefix = null;

    /**
     * @var bool
     */
    public $showLineItemsOnPageLoad = true;

    public function init()
    {
        parent::init();

        if (Yii::$app->request->post(self::ACTION_PARAM_APPEND_ITEM, false)) {
            $this->_actionAppendLine = true;
        }

        if (!$this->_actionAppendLine) {
            if (empty($this->panelOptions['id']))
                $this->panelOptions['id'] = $this->activeForm->getId() . '-panel';
            if (empty($this->finishButtonOptions['id']))
                $this->finishButtonOptions['id'] = $this->activeForm->getId() . '-finish-button';
            $this->finishButtonOptions['type'] = 'button';
        }

        if (!empty($this->lineItemModels)) {
            if ($this->lineItemModels instanceof LineItemModelInterface) {
                $model = $this->lineItemModels;
                $this->lineItemModels[] = $model;
            } else {
                $model = $this->lineItemModels[0];
            }
            if (empty($this->_lineItemFields)) {
                $this->_lineItemFields = $model->lineItemFields();
            }
            if (empty($this->_lineItemFieldsLabels)) {
                $this->_lineItemFieldsLabels = $model->lineItemFieldsLabels();
            }

            if ($index = Yii::$app->request->post(self::ACTION_PARAM_NEXT_ITEM_INDEX, false)) {
                $this->nextItemIndex = $index;
            }

            $currentUrl = Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->route], Yii::$app->controller->actionParams));
            if (empty($this->addLineItemUrl))
                $this->addLineItemUrl = $currentUrl;
            if (empty($this->saveLineItemUrl))
                $this->saveLineItemUrl = $currentUrl;
            if (empty($this->deleteLineItemUrl))
                $this->deleteLineItemUrl = $currentUrl;
            $this->_itemTrIdPrefix = $model->formName();
        }
    }

    public function run()
    {
        if ($this->_actionAppendLine && !empty($this->lineItemModels)) {
            echo $this->getTbodyTrHtml($this->lineItemModels[0]);
        } else {
            if (empty($this->lineItemModels)) {
                $inner_html = "";
            } else {
                if ($this->showPanel) {
                    //{title} {lineItemsCount}
                    $panelTitleTemplate = $this->beforeLineItemsTemplate;
                    $title = strtr($panelTitleTemplate, [
                        '{title}' => $this->title,
                        '{lineItemsCount}' => count($this->lineItemModels),
                    ]);
                    $title = Html::tag('h3', $title, $this->panelTitleOptions);
                    $heading = Html::tag('div', $title, $this->panelHeadingOptions);
                    $inner_html = Html::beginTag('div', $this->panelOptions);
                    $inner_html .= $heading;
                    $inner_html .= $this->getTableHtml();
                    $footer_contents = $this->getAddButtonHtml();
                    if (!empty($footer_contents)) {
                        $footer = Html::tag('div', $footer_contents, $this->panelFooterOptions);
                        $inner_html .= $footer;
                    }
                    $inner_html .= Html::endTag('div');

                } else {
                    $inner_html = $this->getTableHtml();
                }
            }

            $inner_html = strtr($this->template, [
                '{items}' => $inner_html,
                '{finishButton}' => Html::tag('button', $this->finishButtonLabel, $this->finishButtonOptions),
            ]);

            if (null !== $this->parentModel) {
                echo $this->activeForm->field($this->parentModel, $this->parentPrimaryKeyAttribute)->hiddenInput()->label(false);
                $this->_parentPrimaryKeyFieldSelector = '#' . Html::getInputId($this->parentModel, $this->parentPrimaryKeyAttribute);
            }

            echo $inner_html;

            $this->registerScripts();
        }
    }

    /**
     * @param string $viewPath
     * @param int $count
     * @param array $itemModelConfig should at least has "class"
     * Sample $config
     * ```php
     *   $config=['class'=>$model::class,'order_id'=>3];
     * ```
     * @param mixed $condition
     * @param array $params
     * @return mixed
     */
    public static function addItemAction($viewPath, $itemModelConfig, $count = 1, $condition = null, $params = [])
    {
        if (Yii::$app->request->isPost && $next_index = Yii::$app->request->post(LineItem::ACTION_PARAM_NEXT_ITEM_INDEX)) {
            /* @var $className LineItemModelInterface */
            $className = $itemModelConfig['class'];
            return Yii::$app->controller->renderPartial($viewPath, [
                'lineItemModels' => $className::getLineModels($count, $itemModelConfig, $condition, $params),
            ]);
        }
    }

    /**
     * @param string $modelClass
     * @return string
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\web\NotFoundHttpException
     */
    public static function deleteItemAction($modelClass)
    {
        if (Yii::$app->request->isPost && Yii::$app->request->post(self::ACTION_PARAM_DELETE_ITEM)) {
            $id = Yii::$app->request->post(LineItem::ACTION_PARAM_ITEM_ID);
            if (!empty($id)) {
                /* @var $modelClass ActiveRecord|LineItemModelInterface */
                $model = $modelClass::loadModel($id);
                $model->delete();
                return json_encode(['success' => true, 'message' => 'Success.']);
            } else {
                return json_encode(['success' => false, 'message' => 'Nothing to delete.']);
            }
        }
    }

    /**
     * @param string $modelClass
     * @param array $config
     * @return string
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public static function saveItemsAction($modelClass, $config = [])
    {
        if (Yii::$app->request->isPost && Yii::$app->request->post(LineItem::ACTION_PARAM_SAVE_ITEM)) {
            $response = static::validateItems($modelClass, $config, true);
            return json_encode($response['response']);
        }
    }

    /**
     * @param string $modelClass
     * @param array $config
     * @param bool $saveItems
     * @param null $validationAttributeNames
     * @param array $options
     * See available options below:
     * ```php
     * $options=[
     *     'parentModelAttribute'=>null,//parent model property on the line model
     *     'parentModel'=>null,//the parent model
     * ]
     * ```
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public static function validateItems($modelClass, $config = [], $saveItems = false, $validationAttributeNames = null, $options = [])
    {
        /* @var $model ActiveRecord|ActiveSearchInterface */
        $model = new $modelClass($config);
        $formName = $model->formName();
        $response = [];
        $primaryKeyAttribute = $model::getPrimaryKeyColumn();
        $response['success'] = true;
        $parentModelAttribute = ArrayHelper::getValue($options, 'parentModelAttribute', null);
        $parentModel = ArrayHelper::getValue($options, 'parentModel', null);
        if (!empty($parentModelAttribute)) {
            $model->{$parentModelAttribute} = $parentModel;
        }
        foreach (Yii::$app->request->post($formName, []) as $k => $item) {
            if (!empty($item[$primaryKeyAttribute])) {
                $new_model = $model->loadModel($item[$primaryKeyAttribute]);
            } else {
                $new_model = clone $model;
            }

            $new_model->setAttributes($item);
            if ($new_model->validate($validationAttributeNames)) {
                if ($saveItems) {
                    $new_model->save();
                }
                $response['response'][$k] = [
                    'success' => true,
                    'id' => $new_model->{$primaryKeyAttribute},
                    'model' => $new_model->attributes,
                    'message' => 'Success.'
                ];
            } else {
                $response['success'] = false;
                $errors = $new_model->getErrors();
                $formattedErrors = [];
                foreach ($errors as $attribute => $error) {
                    $formattedErrors[Html::getInputId($new_model, $attribute)] = $error;
                }
                $response['response'][$k] = ['success' => false, 'model' => $new_model->attributes, 'error' => $formattedErrors];
            }

            $response['models'][$k] = $new_model;
        }

        return $response;
    }

    /**
     * @param ActiveRecord $model
     * @param string $lineItemModelClassName
     * @param $itemModelForeignKeyAttribute
     * @param bool $saveParentModel
     * @param array $options
     * See available options below:
     * ```php
     * $options=[
     *     'redirectRoute'=>'view',//the route to redirect to after saving the items. Defaults to "view"
     *     'redirectParams'=>['customer_id'=>4],//pass redirect action params. Defaults to []
     *     'idParam'=>'id',//the new created parent record param name. Defaults to "id"
     *     'idParamAttribute'=>'id', // the parent model attribute to use as idParam value. Defaults to primary key
     *     'parentModelPrimaryKeyAttribute'=>null,//leave this null to default to the table primary key. If you wish to use another column which is not the primary you can specify the field e.g "code"
     *     'parentModelAttribute'=>null,//parent model property on the line model
     * ]
     * ```
     * @return mixed
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public static function finishAction($model, $lineItemModelClassName, $itemModelForeignKeyAttribute, $saveParentModel = true, $options = [])
    {
        $redirectRoute = ArrayHelper::getValue($options, 'redirectRoute', 'view');
        $redirectParams = ArrayHelper::getValue($options, 'redirectParams', []);
        $idParam = ArrayHelper::getValue($options, 'idParam', 'id');
        $idParamAttribute = ArrayHelper::getValue($options, 'idParamAttribute', null);
        $parentModelAttribute = ArrayHelper::getValue($options, 'parentModelAttribute', null);
        $formName = $model->formName();
        if ($data = Yii::$app->request->post($formName, null)) {
            $primaryKeyAttribute = ArrayHelper::getValue($options, 'parentModelPrimaryKeyAttribute', $model::getPrimaryKeyColumn());
            if (!empty($data[$primaryKeyAttribute])) {
                $model = $model->loadModel($data[$primaryKeyAttribute]);
            }
            /* @var $lineItemModel ActiveRecord|LineItemModelInterface */
            $lineItemModel = new $lineItemModelClassName();
            $itemModelPrimaryKeyAttribute = $lineItemModel::getPrimaryKeyColumn();
            $model->setAttributes($data);
            if (!$saveParentModel || $model->validate()) {
                $safeAttributes = array_flip($lineItemModel->safeAttributes());
                if (isset($safeAttributes[$itemModelForeignKeyAttribute])) {
                    unset($safeAttributes[$itemModelForeignKeyAttribute]);
                }
                $safeAttributes = array_flip($safeAttributes);
                $itemsOptions = null !== $parentModelAttribute ? ['parentModelAttribute' => $parentModelAttribute, 'parentModel' => $model] : [];
                $items = static::validateItems($lineItemModelClassName, [], false, $safeAttributes, $itemsOptions);
                if ($items['success']) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($saveParentModel) {
                            $model->save(false);
                        }
                        /* @var $m ActiveRecord|LineItemModelInterface */
                        if (isset($items['models'])) {
                            foreach ($items['models'] as $k => $m) {
                                $m->{$itemModelForeignKeyAttribute} = $model->{$primaryKeyAttribute};
                                $m->save(false);
                                $items['response'][$k]['id'] = $m->{$itemModelPrimaryKeyAttribute};
                                $items['response'][$k]['model'] = $m->attributes;
                            }
                        }

                        $transaction->commit();
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::error($e->getMessage());
                        Yii::error($e->getTrace());
                        if ($e->getMessage() != ''){
                            throw new HttpException(500, $e->getMessage());
                        }
                        throw new HttpException(500, 'Internal server error. Could not save the data. The transaction has been rolled back.');
                    }

                    $idParamValue = !empty($idParamAttribute) ? $model->{$idParamAttribute} : $model->{$primaryKeyAttribute};
                    $redirectUrl = Url::getReturnUrl(array_merge([$redirectRoute, $idParam => $idParamValue], (array)$redirectParams));
                    return Json::encode([
                        'success' => true,
                        'id' => $model->{$primaryKeyAttribute},
                        'model' => $model->attributes,
                        'items' => $items['response'] ?? [],
                        'message' => Lang::t('SUCCESS_MESSAGE'),
                        'redirectUrl' => $redirectUrl,
                    ]);

                } else {
                    return Json::encode([
                        'success' => false,
                        'model' => $model->attributes,
                        'items' => $items['response'],
                        'message' => 'Input validation errors.',
                    ]);
                }
            } else {
                return Json::encode([
                    'success' => false,
                    'model' => $model->attributes,
                    'message' => 'Input validation errors.',
                    'error' => $model->getErrors()
                ]);
            }
        }
    }

    /**
     * @param ActiveRecord|LineItemModelInterface $model
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    protected function getTbodyTrHtml($model)
    {
        $this->tbodyTrOptions['id'] = $model->formName() . '-' . $this->nextItemIndex;
        $trHtml = Html::beginTag('tr', $this->tbodyTrOptions);

        foreach ($this->_lineItemFields as $field) {
            if ($field['type'] === self::LINE_ITEM_FIELD_TYPE_HIDDEN_INPUT) {
                continue;
            }
            $fieldHtml = '';
            $template = !empty($field['template']) && strpos($field['template'], '{input}') ? $field['template'] : '{input}';
            $options = ArrayHelper::getValue($field, 'options', []);
            if (is_callable($options)) {
                $options = $options->call($this, $model);
            }
            $tdOptions = ArrayHelper::getValue($field, 'tdOptions', []);
            if (is_callable($tdOptions)) {
                $tdOptions = $options->call($this, $model);
            }
            if (is_callable($field['type'])) {
                $field['type'] = $field['type']->call($this, $model);
            }
            $input = true;
            if (isset($field['input'])) {
                if (is_callable($field['input'])) {
                    $input = $field['input']->call($this, $model);
                } else {
                    $input = $field['input'];
                }
            }
            if ($input === true) {
                switch ($field['type']) {
                    case self::LINE_ITEM_FIELD_TYPE_TEXT_INPUT:
                        $fieldHtml = $this->activeTextInput($model, $field['attribute'], $options);
                        break;
                    case self::LINE_ITEM_FIELD_TYPE_NUMBER:
                        $fieldHtml = $this->activeNumberInputWidget($model, $field['attribute'], $options);
                        break;
                    case self::LINE_ITEM_FIELD_TYPE_TEXTAREA:
                        $fieldHtml = $this->activeTextarea($model, $field['attribute'], $options);
                        break;
                    case self::LINE_ITEM_FIELD_TYPE_DROP_DOWN_LIST:
                        $listItems = ArrayHelper::getValue($field, 'listItems', []);
                        if (is_callable($listItems)) {
                            $listItems = $listItems->call($this, $model);
                        }

                        $fieldHtml = $this->activeDropDownList($model, $field['attribute'], $listItems, $options);
                        break;
                    case self::LINE_ITEM_FIELD_TYPE_CHECKBOX:
                        $fieldHtml = $this->activeCheckbox($model, $field['attribute'], $options);
                        break;
                    case self::LINE_ITEM_FIELD_TYPE_STATIC:
                        if (array_key_exists('value', $field)) {
                            $value = $field['value'];
                            if (is_callable($value)) {
                                $value = $value->call($this, $model);
                            }

                        } else {
                            $value = $model->{$field['attribute']};
                        }
                        Html::addCssClass($options, 'form-control-static');
                        $fieldHtml = Html::tag('p', $value, $options);
                        break;
                    case self::LINE_ITEM_FIELD_TYPE_FILE:
                        $fieldHtml = null;
                        $fileOptions = ArrayHelper::getValue($field, 'widget', []);
                        if (is_callable($fileOptions)) {
                            $fieldHtml = $fileOptions->call($this, $model, $this->getView(), $this->nextItemIndex);
                        }
                        break;
                }
            } else {
                $fieldHtml = $input;
            }
            $fieldHtml = strtr($template, ['{input}' => $fieldHtml]);
            $trHtml .= Html::tag('td', $fieldHtml, $tdOptions);
        }
        $trHtml .= $this->getActionButtonsHtml($model);
        $trHtml .= Html::endTag('tr');
        $this->nextItemIndex++;
        return $trHtml;
    }

    /**
     * @param ActiveRecord|LineItemModelInterface $model
     * @param string $attribute
     * @param int $index
     * @param array $options
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function getActiveInputOptions($model, $attribute, $index, $options = [])
    {
        $class = Html::getInputId($model, $attribute);
        $options['id'] = $class . '-' . $index;
        $options['class'] = isset($options['class']) ? trim($options['class'] . ' ' . $class) : 'form-control ' . $class;
        $options['name'] = static::getInputName($model, $attribute, $index);
        return $options;
    }

    /**
     * @param ActiveRecord|LineItemModelInterface $model
     * @param string $attribute
     * @param int $index
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getInputName($model, $attribute, $index)
    {
        $formName = $model->formName();
        return $formName . "[$index]" . "[$attribute]";
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function activeTextInput($model, $attribute, $options = [])
    {
        $options = static::getActiveInputOptions($model, $attribute, $this->nextItemIndex, $options);
        return Html::activeTextInput($model, $attribute, $options);
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function activeNumberInputWidget($model, $attribute, $options = [])
    {
        $options = static::getActiveInputOptions($model, $attribute, $this->nextItemIndex, $options);
        return NumberControl::widget([
            'name' => $options['name'],
            'value' => $model->{$attribute},
            'options' => $options,
            'displayOptions' => ['class' => 'form-control kv-monospace text-left'],
        ]);
        // return Html::activeTextInput($model, $attribute, $options);
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function activeTextarea($model, $attribute, $options = [])
    {
        $options = static::getActiveInputOptions($model, $attribute, $this->nextItemIndex, $options);
        return Html::activeTextarea($model, $attribute, $options);
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $items
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function activeDropDownList($model, $attribute, $items = [], $options = [])
    {
        $options = static::getActiveInputOptions($model, $attribute, $this->nextItemIndex, $options);
        return Html::activeDropDownList($model, $attribute, $items, $options);
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function activeCheckbox($model, $attribute, $options = [])
    {
        $options = static::getActiveInputOptions($model, $attribute, $this->nextItemIndex, $options);
        $options['label'] = false;
        return Html::activeCheckbox($model, $attribute, $options);
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function activeHiddenInput($model, $attribute, $options = [])
    {
        $options = static::getActiveInputOptions($model, $attribute, $this->nextItemIndex, $options);
        return Html::activeHiddenInput($model, $attribute, $options);
    }

    /**
     * @param ActiveRecord $model
     * @param string $attribute
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function activeFileInput($model, $attribute, $options = [])
    {
        $options = static::getActiveInputOptions($model, $attribute, $this->nextItemIndex, $options);
        return Html::activeFileInput($model, $attribute, $options);
    }

    /**
     * @param ActiveRecord|LineItemModelInterface $model
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    protected function getActionButtonsHtml($model)
    {
        if (null === $this->lineItemActionButtonsTemplate)
            $this->lineItemActionButtonsTemplate = '{save_button}&nbsp;&nbsp;{delete_button}';

        $this->saveButtonOptions['title'] = Lang::t('Save');
        $this->saveButtonOptions['class'] = empty($this->saveButtonOptions['class']) ? $this->saveButtonCssClass : $this->saveButtonOptions['class'] . ' ' . $this->saveButtonCssClass;
        $save_button_class = $model->getIsNewRecord() ? 'text-warning' : 'text-success';
        $this->saveButtonOptions['class'] .= ' ' . $save_button_class;
        $this->saveButtonOptions['data-href'] = $this->saveLineItemUrl;
        if (!$this->showSaveButton) {
            $this->saveButtonOptions['class'] .= ' hidden';
        }
        $save_button = Html::a($this->saveButtonLabel, 'javascript:void(0);', $this->saveButtonOptions);
        $delete_button = '';
        if ($this->showDeleteButton) {
            $this->deleteButtonOptions['title'] = Lang::t('Delete');
            $this->deleteButtonOptions['class'] = empty($this->deleteButtonOptions['class']) ? $this->deleteButtonCssClass : $this->deleteButtonOptions['class'] .= ' ' . $this->deleteButtonCssClass;
            //$this->deleteButtonOptions['data-delete-confirm'] = Lang::t('Are you sure you want to delete this item?');
            $this->deleteButtonOptions['data-href'] = $this->deleteLineItemUrl;
            $delete_button = Html::a($this->deleteButtonLabel, 'javascript:void(0);', $this->deleteButtonOptions);
        }

        $inner_html = strtr($this->lineItemActionButtonsTemplate, [
            '{save_button}' => $save_button,
            '{delete_button}' => $delete_button,
        ]);

        $inner_html .= $this->activeHiddenInput($model, $this->primaryKeyAttribute, []);
        if (empty($this->_primaryKeyFieldSelector)) {
            $this->_primaryKeyFieldSelector = '.' . Html::getInputId($model, $this->primaryKeyAttribute);
        }
        if (!empty($this->foreignKeyAttribute)) {
            $inner_html .= $this->activeHiddenInput($model, $this->foreignKeyAttribute, []);
            if (empty($this->_foreignKeyFieldSelector)) {
                $this->_foreignKeyFieldSelector = '.' . Html::getInputId($model, $this->foreignKeyAttribute);
            }
        }

        foreach ($this->_lineItemFields as $field) {
            $options = ArrayHelper::getValue($field, 'options', []);
            if (is_callable($field['type'])) {
                $field['type'] = $field['type']->call($this, $model);
            }
            switch ($field['type']) {
                case self::LINE_ITEM_FIELD_TYPE_HIDDEN_INPUT:
                    $inner_html .= $this->activeHiddenInput($model, $field['attribute'], $options);
                    break;
            }

        }
        return Html::tag('td', $inner_html, $this->lineItemActionButtonsWrapperOptions);
    }

    protected function getTableHtml()
    {
        $template = '{thead}{tbody}';
        $content = strtr($template, [
            '{thead}' => $this->getTHeadHtml(),
            '{tbody}' => $this->getTbodyHtml(),
        ]);
        if (empty($this->tableOptions['id'])) {
            $this->tableOptions['id'] = $this->activeForm->getId() . '-table';
        }
        return Html::tag('table', $content, $this->tableOptions);
    }

    /***
     * @return string
     */
    protected function getTHeadHtml()
    {
        $tds = '';
        foreach ($this->_lineItemFieldsLabels as $label) {
            if (is_array($label)) {
                $tds .= Html::tag('th', $label['label'], isset($label['options']) ? $label['options'] : []);
            } else {
                $tds .= Html::tag('th', $label, []);
            }
        }

        $tr = Html::tag('tr', $tds, $this->theadTrOptions);

        return Html::tag('thead', $tr, $this->theadOptions);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    protected function getTbodyHtml()
    {
        $tbodyContent = '';
        foreach ($this->lineItemModels as $model) {
            if ($this->showLineItemsOnPageLoad) {
                $tr = $this->getTbodyTrHtml($model);
                $tbodyContent .= $tr;
            }
        }

        return Html::tag('tbody', $tbodyContent, $this->tbodyOptions);
    }

    /**
     * @return string
     */
    protected function getAddButtonHtml()
    {
        if (!$this->showAddLineButton) {
            return "";
        }

        if (empty($this->addLineItemButtonOptions['id']))
            $this->addLineItemButtonOptions['id'] = 'add-new-item-line';

        if (empty($this->addLineItemLabel))
            $this->addLineItemLabel = ' <i class="fa fa-plus-circle"></i> ' . Lang::t('Add New Line');
        $this->addLineItemButtonOptions['data-href'] = $this->addLineItemUrl;

        return Html::a($this->addLineItemLabel, 'javascript:void(0);', $this->addLineItemButtonOptions);
    }

    protected function registerScripts()
    {
        $view = $this->getView();
        AssetBundle::register($view);

        $options = [
            'selectors' => [
                'form' => '#' . $this->activeForm->getId(),
                'itemsTable' => isset($this->tableOptions['id']) ? '#' . $this->tableOptions['id'] : null,
                'itemTr' => !empty($this->tbodyTrOptions['class']) ? 'tr.' . $this->tbodyTrOptions['class'] : 'tr.line-item',
                'addLineButton' => !empty($this->addLineItemButtonOptions['id']) ? '#' . $this->addLineItemButtonOptions['id'] : null,
                'panel' => isset($this->panelOptions['id']) ? '#' . $this->panelOptions['id'] : null,
                'deleteItemButton' => '.' . $this->deleteButtonCssClass,
                'primaryKeyField' => $this->_primaryKeyFieldSelector,
                'foreignKeyField' => $this->_foreignKeyFieldSelector,
                'parentPrimaryKeyField' => $this->_parentPrimaryKeyFieldSelector,
                'saveLineItem' => '.' . $this->saveButtonCssClass,
                'finishButton' => isset($this->finishButtonOptions['id']) ? '#' . $this->finishButtonOptions['id'] : null,
            ],
            'actionParamNextIndex' => self::ACTION_PARAM_NEXT_ITEM_INDEX,
            'actionParamAppendItem' => self::ACTION_PARAM_APPEND_ITEM,
            'actionParamDeleteItem' => self::ACTION_PARAM_DELETE_ITEM,
            'actionParamItemId' => self::ACTION_PARAM_ITEM_ID,
            'actionParamSaveItem' => self::ACTION_PARAM_SAVE_ITEM,
            'itemTrIdPrefix' => $this->_itemTrIdPrefix,
            'parentModelShortClassName' => null !== $this->parentModel ? $this->parentModel->shortClassName() : null,
        ];

        foreach (['beforeFinish', 'afterFinish', 'afterSave', 'beforeSave', 'beforeDelete', 'afterDelete', 'afterAdd'] as $event) {
            if ($this->$event !== null) {
                if ($this->$event instanceof JsExpression)
                    $options['events'][$event] = $this->$event;
                else
                    $options['events'][$event] = new JsExpression($this->$event);
            }
        }

        $view->registerJs("MyApp.widget.initLineItem(" . Json::encode($options) . ")");
    }
}