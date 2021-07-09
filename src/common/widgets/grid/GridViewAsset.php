<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/01
 * Time: 8:46 PM
 */

namespace common\widgets\grid;


use backend\assets\AppAsset;
use yii\web\AssetBundle;

class GridViewAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        parent::init();
    }

    public $js = [
    ];

    public $css = [
        'css/custom.css',
    ];
    public $depends = [
        AppAsset::class,
    ];
}