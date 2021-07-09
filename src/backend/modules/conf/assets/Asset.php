<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-05-22 7:38 PM
 */

namespace backend\modules\conf\assets;


use backend\assets\AppAsset;
use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@confModule/assets/src';

    public $js = [
        'js/conf-module.js',
    ];

    public $depends = [
        AppAsset::class,
    ];
}