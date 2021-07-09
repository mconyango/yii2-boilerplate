<?php
namespace common\widgets\smartSelect;

use common\models\ActiveRecord;
use yii\bootstrap\Html;
use yii\helpers\Json;

/**
 * Smart DropDown extension
 *
 * @author Fred <mconyango@gmail.com>
 */
class SmartSelect extends \yii\base\Widget
{

    /**
     * @var ActiveRecord the model owning the form
     */
    public $model;

    /**
     * @var ActiveRecord|string The model object or class name used to generate dropdown list
     */
    public $optionsModel;
    /**
     * @var string The field name for select (dropdown list)
     */
    public $attribute;
    /**
     * @var string The field name for the input (for creating a new item on the fly)
     */
    public $inputAttribute;
    /**
     * @var array select html options
     */
    public $selectOptions = ['class' => 'form-control'];
    /**
     * @var array html options for the generated input
     */
    public $inputOptions = ['class' => 'form-control', 'style' => 'margin-top:10px;'];

    /**
     * @var array data used to populate select options
     */
    public $selectData = [];
    /**
     * @var bool whether to show the select field label
     */
    public $showLabel = true;

    /**
     * @var array html options for select label
     */
    public $labelOptions = ['class' => 'col-sm-2 control-label'];

    /**
     * @var array
     */
    public $containerHtmlOptions = ['class' => 'form-group'];
    /**
     * @var string base template
     */
    public $template = '{{label}}{{field}}{{link}}';
    /**
     * @var array html options for the field container
     */
    public $fieldWrapperOptions = ['class' => 'col-sm-6'];
    /**
     * @var string
     */
    public $fieldTemplate = '{{select}} {{input}}';

    /**
     * @var array html options for the input wrapper
     */
    private $inputWrapperOptions = [];

    /**
     * @var bool
     */
    public $showAddLink = true;

    /**
     * @var string label for the create link
     */
    public $linkLabel = '<i class="fa fa-2x fa-plus-circle"></i>';
    /**
     * @var array html options for the create link
     */
    public $linkOptions = ['title' => 'Click to add new'];
    /**
     * @var array the url for creating new list item
     */
    public $url;
    /**
     * @var string template for generating the link
     */
    public $linkTemplate = '<p>{{link}}</p><p>{{spinner}}</p>';
    /**
     * @var array html options for the link
     */
    public $linkWrapperOptions = ['class' => 'col-sm-1'];

    public function init()
    {
        if (is_string($this->optionsModel)) {
            $this->optionsModel = new $this->optionsModel();
        }
        $options_model_class = strtolower($this->optionsModel->shortClassName());
        $model_class = strtolower($this->model->shortClassName());

        if (!isset($this->containerHtmlOptions['class'])) {
            $this->containerHtmlOptions['class'] = 'form-group';
        }
        $this->containerHtmlOptions['id'] = "field-{$model_class}-" . $this->attribute;

        $this->inputWrapperOptions = [
            'id' => $options_model_class . '-' . $this->inputAttribute . '-wrapper',
            'style' => 'display:none;',
        ];
        $this->linkWrapperOptions['id'] = $options_model_class . '-' . $this->inputAttribute . '-create';
        $this->selectOptions['id'] = $model_class . '-' . $this->attribute;
        $this->inputOptions['id'] = $options_model_class . '-' . $this->inputAttribute;

        parent::init();
    }

    public function run()
    {
        echo $this->processTemplate();
        $this->registerAssets();
    }

    protected function processTemplate()
    {
        $field = Html::tag('div', $this->template, $this->containerHtmlOptions);
        return strtr($field, [
            '{{label}}' => $this->generateLabelHtml(),
            '{{field}}' => $this->generateFieldHtml(),
            '{{link}}' => $this->generateLink(),
        ]);
    }

    protected function generateLabelHtml()
    {
        $label = "";
        if ($this->showLabel) {
            $label = Html::activeLabel($this->model, $this->attribute, $this->labelOptions);
        }
        return $label;
    }

    protected function generateFieldHtml()
    {
        $select = Html::activeDropDownList($this->model, $this->attribute, $this->selectData, $this->selectOptions);
        $input = Html::activeTextInput($this->optionsModel, $this->inputAttribute, $this->inputOptions);
        $error = '<p class="text-danger smart-select-error" style="display:none;margin-top: 5px;"></p>';
        $input_wrapper = Html::tag('div', $input . $error, $this->inputWrapperOptions);
        $inner_hml = strtr($this->fieldTemplate, [
            '{{select}}' => $select,
            '{{input}}' => $input_wrapper,
        ]);

        return Html::tag('div', $inner_hml, $this->fieldWrapperOptions);
    }

    protected function generateLink()
    {
        if (!$this->showAddLink) {
            return "";
        }

        $this->linkOptions['data-href'] = $this->url;
        $link = Html::a($this->linkLabel, 'javascript:void(0);', $this->linkOptions);
        $spinner = '<i class="fa fa-2x fa-spinner fa-spin" style="display: none"></i>';
        $inner_html = strtr($this->linkTemplate, [
            '{{link}}' => $link,
            '{{spinner}}' => $spinner,
        ]);

        return Html::tag('div', $inner_html, $this->linkWrapperOptions);
    }

    protected function registerAssets()
    {
        $view = $this->getView();
        AssetBundle::register($view);

        $options = [
            'selectId' => $this->selectOptions['id'],
            'linkWrapperId' => $this->linkWrapperOptions['id'],
            'inputWrapperId' => $this->inputWrapperOptions['id'],
            'inputId' => $this->inputOptions['id'],
        ];
        $view->registerJs("MyApp.plugin.smartDropDown(" . Json::encode($options) . ");");
    }

}
