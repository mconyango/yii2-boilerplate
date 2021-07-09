<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/06/15
 * Time: 10:33 AM
 */

namespace common\widgets\fineuploader;


use backend\assets\AppAsset;
use yii\web\AssetBundle;

class FineuploaderAsset extends AssetBundle
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
        'fine-uploader/fine-uploader.min.js'
    ];
    public $css = [
        'fine-uploader/fine-uploader-new.min.css',
    ];
    public $depends = [
        AppAsset::class
    ];

} 