<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/27
 * Time: 6:50 PM
 */

namespace common\widgets\gmap;


use backend\assets\AppAsset;

class AssetBundle extends \yii\web\AssetBundle
{
    public $js = [
        'js/singleview.js',
        'js/geocode.js',
    ];
    public $css = [
    ];
    public $depends = [
        AppAsset::class,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        parent::init();
    }
}