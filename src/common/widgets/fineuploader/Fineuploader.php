<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/06/14
 * Time: 7:19 PM
 */

namespace common\widgets\fineuploader;


use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\AssetBundle;
use yii\web\JsExpression;
use yii\web\View;

class Fineuploader extends Widget
{
    public $containerId = 'fine-uploader-container';

    public $index = 1;

    public $dropLabel = 'Drop here.';
    public $dropProcessingLabel = 'Processing dropped file(s)...';

    public $buttonIcon = 'fa fa-open';
    public $buttonLabel = 'Add File';
    public $cancelLabel = 'Cancel';
    public $retryLabel = 'Retry';
    public $deleteLabel = 'Delete';


    public $options = [];
    private $defaultOptions = [
        'template' => 'qq-template'
    ];

    public $callBacks = [];

    const FILE_TYPE_IMAGE = 1;
    const FILE_TYPE_EXCEL = 2;
    const FILE_TYPE_OTHERS = 3;

    /**
     * @var string
     */
    public $fileType;

    /**
     * @var string
     */
    public $fileSelector;

    /**
     * @var string
     */
    public $excelSheetSelector;

    /**
     * @var string
     */
    public $alertSelector;

    /**
     * @var string
     */
    public $template;

    /**
     * @var AssetBundle
     */
    private $_assetBundle;

    /**
     * @var string
     */
    private $_script;


    public function init()
    {
        parent::init();
        $this->options = array_merge($this->defaultOptions, $this->options);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerPlugin();
        $this->registerJS();
        echo Html::tag('div', "", ['id' => '_raw_script_' . $this->index, 'data-content' => $this->_script, 'class' => 'hidden']);

        echo $this->getView()->render(!empty($this->template)?$this->template:'@common/widgets/fineuploader/views/default', ['widget' => $this]);
    }

    /**
     * Registers plugin and the related events
     */
    protected function registerPlugin()
    {
        $view = $this->getView();
        $this->_assetBundle = FineuploaderAsset::register($view);
    }

    /**
     * Register JS
     */
    protected function registerJS()
    {
        $this->registerCoreEvents();
        $options = $this->options;
        $options['callbacks'] = $this->callBacks;
        $options['element'] = new JsExpression('document.getElementById("' . $this->containerId . '")');
        $options['text'] = [
            'fileInputTitle' => '',
        ];
        $options['thumbnails'] = [
            'placeholders' => [
                'waitingPath' => $this->_assetBundle->baseUrl . '/fine-uploader/placeholders/waiting-generic.png',
                'notAvailablePath' => $this->_assetBundle->baseUrl . '/fine-uploader/placeholders/not_available-generic.png'
            ]
        ];
        $options = Json::encode($options);
        $this->_script = "var uploader{$this->index} = new qq.FineUploader({$options})";
        $this->getView()->registerJs($this->_script, View::POS_READY, __CLASS__ . $this->containerId);

    }

    protected function registerCoreEvents()
    {
        if (empty($this->callBacks['onComplete'])) {
            if ($this->fileType === self::FILE_TYPE_EXCEL) {
                $this->callBacks['onComplete'] = new JsExpression('
                  function(id, name, responseJSON){
                     var $=jQuery;
                     if(responseJSON.success){
                         $("' . $this->fileSelector . '").val(responseJSON.path);
                         MyApp.plugin.excel.setSheets("' . $this->excelSheetSelector . '",responseJSON);
                       }else{
                          MyApp.utils.showAlertMessage(responseJSON.error,"error","' . $this->alertSelector . '");
                     }
                  }'
                );
            } else {
                $this->callBacks['onComplete'] = new JsExpression(
                    'function(id, name, responseJSON){
                      console.log(responseJSON);
                      var $=jQuery;
                      if(responseJSON.success){
                         $("' . $this->fileSelector . '").val(responseJSON.path);
                      }else{
                         MyApp.utils.showAlertMessage(responseJSON.error,"error","' . $this->alertSelector . '");
                      }
                    }'
                );
            }
        }

        if (empty($this->callBacks['onDeleteComplete'])) {
            if ($this->fileType === self::FILE_TYPE_EXCEL) {
                $this->callBacks['onDeleteComplete'] = new JsExpression(
                    'function(id,xhr,isError){
                        var $=jQuery;
                        if(!isError){
                          $("' . $this->fileSelector . '").val("");
                          $("' . $this->excelSheetSelector . '").val("").trigger("change");
                        }
                    }'
                );
            } else {
                $this->callBacks['onDeleteComplete'] = new JsExpression(
                    'function(id,xhr,isError){
                        var $=jQuery;
                        if(!isError){
                          $("' . $this->fileSelector . '").val("");
                        }
                    }'
                );
            }
        }
    }
}