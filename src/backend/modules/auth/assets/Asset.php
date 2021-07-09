<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-05-17 6:15 PM
 */

namespace backend\modules\auth\assets;


use backend\assets\AppAsset;
use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@authModule/assets/src';

    public $js = [
        'js/auth-module.js',
    ];

    public $depends = [
        AppAsset::class,
    ];
}