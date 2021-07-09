<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/08/23
 * Time: 8:22 PM
 */

use common\widgets\highchart\HighChart;

/* @var $this \yii\web\View */
/* @var $graphFilterOptions array */

$graphType = isset($graphType) ? $graphType : HighChart::GRAPH_LINE;
$dateRange = isset($dateRange) ? $dateRange : HighChart::getDefaultDateRange('Y/m/d');
?>

<?= HighChart::widget([
    'modelClass' => \backend\modules\core\models\LoadingSummaryReport::class,
    'graphType' => $graphType,
    'chartCount' => 1,
    'graphTitle' => 'Loading summary trends',
    'yAxisLabel' => 'Quantity loaded @20C',
    //'chartTemplate' => '{chart}',
    'filterFormAction' => ['graph', 'option' => 1],
    'htmlOptions' => ['class' => 'well well-sm well-light'],
    'queryOptions' => [
        'condition' => '',
        'params' => [],
        'dateField' => 'loading_date',
        'dateRange' => $dateRange,//date range of query eg '2015-01-01 - 2015-12-31'
        'sum' => false,
        'enforceDate' => false,
    ],
]) ?>