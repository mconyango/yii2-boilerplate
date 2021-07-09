<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Class BowerAsset
 * Manage assets pulled in by bower
 *
 * @package common\assets
 */
class BowerAsset extends AssetBundle
{
    public $sourcePath = '@bower';

    public $css = [
        'fontawesome/css/font-awesome.min.css',
        'bootstrap3-dialog/dist/css/bootstrap-dialog.css',
        'select2/dist/css/select2.min.css',
        'jquery-ui/themes/smoothness/jquery-ui.min.css',
        'Ionicons/css/ionicons.min.css',
    ];

    public $js = [
        'highcharts/highcharts.js',
        'highcharts/highcharts-3d.js',
        'highcharts/modules/exporting.js',
        'bootstrap-timepicker/js/bootstrap-timepicker.js',
        'blockUI/jquery.blockUI.js',
        'jquery-ui/jquery-ui.min.js',
        'bootstrap3-dialog/dist/js/bootstrap-dialog.js',
        'select2/dist/js/select2.min.js',
        'PACE/pace.min.js',
        'scrollup/dist/jquery.scrollUp.min.js',
        'typeahead.js/dist/typeahead.bundle.min.js',
        'handlebars/handlebars.min.js',
    ];
}