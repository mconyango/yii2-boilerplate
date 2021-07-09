<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/08
 * Time: 6:19 PM
 */

namespace common\widgets\smartSelect;


use backend\assets\AppAsset;

class AssetBundle extends \yii\web\AssetBundle
{
    /**
     * Initializes the bundle.
     * If you override this method, make sure you call the parent implementation in the last.
     */
    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        parent::init();
    }

    public $js = [
        'js/smart-select.js',
    ];

    public $css = [
    ];

    public $depends = [
        AppAsset::class,
    ];
}