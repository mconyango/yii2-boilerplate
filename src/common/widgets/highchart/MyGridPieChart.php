<?php

/**
 * Description of MyGridPie
 *
 * @author mconyango <mconyango@gmail.com>
 */
class MyGridPieChart extends CWidget
{

    /**
     *
     * @var type
     */
    public $modelClass;

    /**
     *
     * @var type
     */
    public $id;

    /**
     *
     * @var type
     */
    public $filters = [];

    /**
     * htmlOptions of the pie container
     */
    public $htmlOptions = ['class' => 'grid-piechart'];

    /**
     *
     */
    private $series;

    public function init()
    {
        $this->htmlOptions['id'] = 'gridPieChart_' . $this->id;
        parent::init();
    }

    public function run()
    {
        $data = HighChartsDataProvider::getData($this->modelClass, ['filters' => $this->filters], ['graphType' => HighChartsHelper::GRAPH_PIE, 'title' => null], 12, null, false);
        $this->series = $data['series'];
        echo CHtml::tag('div', $this->htmlOptions, "");
        $this->registerScripts();
    }

    public function registerScripts()
    {
        $assets = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assets_url = Yii::app()->assetManager->publish($assets, false, -1, YII_DEBUG ? true : null);
        Yii::app()->clientScript
                ->registerScriptFile($assets_url . '/Highcharts-4.1.6/js/highcharts.js', CClientScript::POS_END)
            ->registerScriptFile($assets_url . '/gridpiechart.js', CClientScript::POS_END)
            ->registerScript('MyGridPieChart-' . $this->id, "MyApp.plugin.gridPieChart('" . $this->htmlOptions['id'] . "'," . json_encode($this->series) . ")");
    }

}
