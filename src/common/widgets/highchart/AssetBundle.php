<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/21
 * Time: 12:37 PM
 */

namespace common\widgets\highchart;


class AssetBundle extends \yii\web\AssetBundle
{
    public $js = [
        'custom.js',
    ];
    public $css = [
        'custom.css',
    ];
    public $depends = [
        BowerAsset::class,
    ];

    /**
     * Initializes the bundle.
     * If you override this method, make sure you call the parent implementation in the last.
     */
    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        parent::init();
    }

}