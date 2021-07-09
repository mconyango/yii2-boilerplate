<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2018-11-23 14:34
 */

namespace backend\assets;

use backend\modules\conf\settings\SystemSettings;
use yii\bootstrap\BootstrapAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\YiiAsset;


class AppAsset extends AssetBundle
{
    public $sourcePath = '@backendAssets/assets';

    public function init()
    {
        parent::init();

        $theme = SystemSettings::getDefaultTheme();
        $this->css = [
            'css/typeaheadjs.css',
            'css/reports.css',
            'css/print.css',
            'css/spacing.css',
            'css/custom.css',
            'css/themes/' . $theme . '.css',
            'css/overrides.css',
        ];
        $this->js = [
            'js/myapp.js',
            'js/plugins.js',
            'js/script.js',
        ];
    }

    public $depends = [
        JqueryAsset::class,
        YiiAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        BowerAsset::class,
        ThemeAsset::class,
    ];
}
