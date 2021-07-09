<?php

namespace common\widgets\grid;

use backend\modules\conf\settings\SystemSettings;
use common\helpers\DateUtils;
use common\helpers\Lang;
use common\models\ActiveRecord;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/01
 * Time: 8:43 PM
 *
 * Wrapper for gridview extension by Kartik Visweswaran <kartikv2@gmail.com>
 */
class GridView extends \kartik\grid\GridView
{
    /**
     * Boolean Icons
     */
    const ICON_ACTIVE = '<span class="fa fa-check text-success"></span>';
    const ICON_INACTIVE = '<span class="fa fa-check text-danger"></span>';

    /**
     * Expand Row Icons
     */
    const ICON_EXPAND = '<span class="fa fa-expand"></span>';
    const ICON_COLLAPSE = '<span class="fa fa-compress"></span>';
    const ICON_UNCHECKED = '<span class="fa fa-unchecked"></span>';

    public $pjax = true;
    public $condensed = true;
    public $hover = true;
    public $floatHeader = false;
    public $bootstrap = true;
    public $bordered = true;
    public $striped = true;
    public $responsive = true;
    public $showPageSummary = false;
    public $title;
    public $panel = [
        'type' => GridView::TYPE_DEFAULT,
        'after' => false,
    ];
    public $persistResize = false;
    // set export properties
    public $export = [
        'fontAwesome' => true,
        'icon' => '',
        'label' => '<i class="fa fa-download"></i>',
    ];

    public $headerRowOptions = ['class' => 'kartik-sheet-style'];
    public $filterRowOptions = ['class' => 'kartik-sheet-style'];
    public $beforeHeader = [];
    public $formatter = ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''];

    //custom attributes
    public $showRefreshButton = true;
    public $refreshUrl;
    public $showExportButton = true;
    /**
     * @var
     */
    public $toolbarButtons = [];

    /**
     * @var ActiveRecord
     */
    public $searchModel;

    /**
     * @var
     */
    public $createButton = [];

    public $tableOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!empty($this->searchModel) && empty($this->dataProvider)) {
            $this->dataProvider = $this->searchModel->search();
        }
        if (!empty($this->searchModel)) {
            $this->id = $this->searchModel->getGridViewWidgetId();
        }

        if (empty($this->title))
            $this->title = $this->getView()->title;
        $this->panel['heading'] = $this->title;
        $this->setToolbar();
        $this->setReplaceTags();
        $this->toggleDataOptions = [
            'maxCount' => 10000,
            'minCount' => 500,
            'confirmMsg' => Lang::t(
                'There are {totalCount} records. Are you sure you want to display them all?',
                ['totalCount' => number_format($this->dataProvider->getTotalCount())]
            ),
            'all' => [
                'icon' => '',
                'label' => '<i class="fa fa-expand"></i> ' . Lang::t('All'),
                'class' => 'btn btn-default',
                'title' => Lang::t('Show all data')
            ],
            'page' => [
                'icon' => '',
                'label' => '<i class="fa fa-compress"></i> ' . Lang::t('Page'),
                'class' => 'btn btn-default',
                'title' => Lang::t('Show first page data')
            ],
        ];
        $this->exportConfig = [
            self::CSV => [],
            self::EXCEL => [],
            self::PDF => [],
            self::JSON => [],
            self::TEXT => [],
            self::HTML => [],
        ];
        $title = Lang::t('Grid Export');
        $pdfHeader = [
            'L' => [
                'content' => $this->title,
                'font-size' => 8,
                'color' => '#333333',
            ],
            'C' => [
                'content' => $title,
                'font-size' => 16,
                'color' => '#333333',
            ],
            'R' => [
                'content' => Lang::t('Generated') . ': ' . DateUtils::formatToLocalDate(date(time()), "D, d-M-Y g:i a"),
                'font-size' => 8,
                'color' => '#333333',
            ],
        ];
        $pdfFooter = [
            'L' => [
                'content' => Lang::t('© {app}', ['app' => SystemSettings::getAppName()]),
                'font-size' => 8,
                'font-style' => 'B',
                'color' => '#999999',
            ],
            'R' => [
                'content' => '[ {PAGENO} ]',
                'font-size' => 10,
                'font-style' => 'B',
                'font-family' => 'serif',
                'color' => '#333333',
            ],
            'line' => true,
        ];
        $this->exportConfig[self::PDF] = [
            'showHeader' => true,
            'showPageSummary' => true,
            'showFooter' => true,
            'showCaption' => true,
            'config' => [
                'mode' => 'UTF-8',
                'format' => 'A4-L',
                'destination' => 'D',
                'marginTop' => 20,
                'marginBottom' => 20,
                'cssInline' => '.kv-wrap{padding:20px}',
                'methods' => [
                    'SetHeader' => [
                        ['odd' => $pdfHeader, 'even' => $pdfHeader],
                    ],
                    'SetFooter' => [
                        ['odd' => $pdfFooter, 'even' => $pdfFooter],
                    ],
                ],
                'options' => [
                    'title' => $title,
                    'subject' => Lang::t('PDF export generated by {app}', ['app' => SystemSettings::getAppName()]),
                    'keywords' => Lang::t('Yii2, grid, export, pdf'),
                ],
                'contentBefore' => '',
                'contentAfter' => '',
            ],
        ];

        parent::init();
    }

    /**
     *
     */
    public function generateCreateButton()
    {
        $view = $this->getView();
        //create button
        $this->createButton['visible'] = ArrayHelper::getValue($this->createButton, 'visible', true);
        if ($this->createButton['visible']) {
            $create_button_url = ArrayHelper::getValue($this->createButton, 'url', Url::to(array_merge(['create'], Yii::$app->request->queryParams)));
            $create_button_label = ArrayHelper::getValue($this->createButton, 'label', '<i class="fa fa-plus-circle"></i> ' . Lang::t('Add ' . $view->context->resourceLabel));
            $create_button_html_options = ArrayHelper::getValue($this->createButton, 'options', ['class' => 'btn btn-default', 'data-pjax' => 0]);
            $create_button_modal = ArrayHelper::getValue($this->createButton, 'modal', false);
            if ($create_button_modal) {
                $new_class = 'show_modal_form';
                if (empty($create_button_html_options['class']))
                    $create_button_html_options['class'] = $new_class;
                else
                    $create_button_html_options['class'] .= ' ' . $new_class;
                $create_button_html_options['data-grid'] = $this->id . '-pjax';
            }

            array_push($this->toolbarButtons, Html::a($create_button_label, $create_button_url, $create_button_html_options));
        }


    }


    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function run()
    {
        //do some stuff here
        parent::run();
    }

    /**
     * Registers client assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        //register custom assets here
        GridViewAsset::register($view);
        parent::registerAssets();
    }

    protected function setToolbar()
    {
        if (($key = array_search('{export}', $this->toolbar)) !== false) {
            unset($this->toolbar[$key]);
        }
        if (($key = array_search('{toggleData}', $this->toolbar)) !== false) {
            unset($this->toolbar[$key]);
        }
        $this->generateCreateButton();
        $buttons = $this->toolbarButtons;

        if (!empty($buttons)) {
            array_push($this->toolbar, [
                'content' => implode(' ', $buttons),
            ]);
        }

        array_push($this->toolbar, '{refreshButton}');

        if ($this->showExportButton) {
            array_push($this->toolbar, '{export}');
        }

        array_push($this->toolbar, '{toggleData}');
    }

    /**
     * @return string
     */
    protected function generateRefreshButton()
    {
        $button = '';
        if ($this->showRefreshButton) {
            $template = '<div class="btn-group">{button}</div>';

            $button = strtr($template, [
                '{button}' => Html::a('<i class="fa fa-repeat"></i>', empty($this->refreshUrl) ? Yii::$app->getUrlManager()->createUrl(array_merge([Yii::$app->controller->route], Yii::$app->controller->actionParams)) : $this->refreshUrl, ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => Lang::t('Refresh Grid')]),
            ]);
        }

        return $button;
    }


    protected function setReplaceTags()
    {
        $this->replaceTags = [
            '{refreshButton}' => $this->generateRefreshButton(),
        ];
    }
}