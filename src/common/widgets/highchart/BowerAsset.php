<?php
/**
 * Created by PhpStorm.
 * User: mconyango
 * Date: 1/27/16
 * Time: 12:53 PM
 */

namespace common\widgets\highchart;


class BowerAsset extends \yii\web\AssetBundle
{

    public $sourcePath = '@bower';

    public $css = [
        'bootstrap-daterangepicker/daterangepicker.css',
    ];

    public $js = [
        'highcharts/highcharts.js',
        'highcharts/highcharts-3d.js',
        'highcharts/modules/exporting.js',
        'moment/min/moment.min.js',
        'bootstrap-daterangepicker/daterangepicker.js',
    ];

    public $depends = [
        \backend\assets\AppAsset::class,
    ];
}