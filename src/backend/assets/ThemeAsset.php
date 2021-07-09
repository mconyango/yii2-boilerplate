<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 23/11/18
 * Time: 14:34
 */

namespace backend\assets;


use Yii;
use yii\web\AssetBundle;

// set @themes alias so we do not have to update baseUrl every time we change themes
Yii::setAlias('@themes', Yii::$app->view->theme->baseUrl);

class ThemeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@themes';

    public function init()
    {
        parent::init();
        $this->css = [
            'smartadmin/css/smartadmin-production.min.css',
            'smartadmin/css/smartadmin-skins.min.css',
        ];
        $this->js = [
            'smartadmin/js/app.config.seed.js',
            'smartadmin/js/app.seed.js',
        ];
    }

    public $depends = [
    ];
}