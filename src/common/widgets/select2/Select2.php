<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 23/11/18
 * Time: 00:52
 */

namespace common\widgets\select2;


use yii\web\JsExpression;

class Select2 extends \kartik\select2\Select2
{
    /**
     * @var bool
     */
    public $modal = false;

    /**
     * @var string
     */
    public $dropdownParentSelector;

    public function run()
    {
        if (!empty($this->dropdownParentSelector)) {
            $this->pluginOptions['dropdownParent'] = new JsExpression("$('{$this->dropdownParentSelector}')");
        } elseif ($this->modal) {
            $this->pluginOptions['dropdownParent'] = new JsExpression("$('#my_bs_modal')");
        }
        parent::run();
    }

}