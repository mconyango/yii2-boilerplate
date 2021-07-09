<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-11-23 15:30
 * Time: 15:30
 */

namespace backend\assets;


use yii\web\AssetBundle;

class ModulesAsset extends AssetBundle
{
    public $depends = [
        AppAsset::class,
        \backend\modules\auth\assets\Asset::class,
        \backend\modules\conf\assets\Asset::class,
        \backend\modules\dashboard\assets\Asset::class,
    ];
}