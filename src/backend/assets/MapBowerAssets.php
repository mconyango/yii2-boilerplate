<?php
/**
 * Author: Fred <mconyango@gmail.com>
 * Date: 2018-03-13
 * Time: 12:02 AM
 */

namespace backend\assets;


use yii\web\AssetBundle;

class MapBowerAssets extends AssetBundle
{
    public $sourcePath = '@bower';

    public $css = [];
    public $js = [
        //'highcharts/js/highmaps.js',
        'jquery-ui.combobox/lib/jquery-ui.combobox.js',
    ];

    public $depends = [
        AppAsset::class,
    ];
}