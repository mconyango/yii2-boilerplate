<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 3:01 AM
 */

namespace common\widgets\grid;

use common\helpers\Lang;
use common\models\ActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ActionColumn extends \kartik\grid\ActionColumn
{

    public $width = '100px';

    /**
     * Render default action buttons
     *
     * @return string
     */
    protected function initDefaultButtons()
    {
        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url) {
                $options = $this->viewOptions;
                if (isset($this->viewOptions['visible']) && $this->viewOptions['visible'] === false) {
                    return '';
                }
                $title = Lang::t('View');
                $icon = '<span class="fa fa-eye"></span>';
                $label = ArrayHelper::remove($options, 'label', ($this->_isDropdown ? $icon . ' ' . $title : $icon));
                $options = ArrayHelper::merge(['title' => $title, 'data-pjax' => '0'], $options);
                if ($this->_isDropdown) {
                    $options['tabindex'] = '-1';
                    return '<li>' . Html::a($label, $url, $options) . '</li>' . PHP_EOL;
                } else {
                    return Html::a($label, $url, $options);
                }
            };
        }
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model) {
                /* @var $model ActiveRecord */
                $options = $this->updateOptions;
                if (isset($this->updateOptions['visible']) && $this->updateOptions['visible'] === false) {
                    return '';
                }
                $title = Lang::t('Update');
                $icon = '<span class="fa fa-pencil text-success"></span>';
                $label = ArrayHelper::remove($options, 'label', ($this->_isDropdown ? $icon . ' ' . $title : $icon));
                $options = ArrayHelper::merge(['title' => $title, 'data-pjax' => '0', 'class' => 'show_modal_form', 'data-grid' => $model->getPjaxWidgetId()], $options);
                $visible = (isset($this->updateOptions['visible']) && $this->updateOptions['visible']) || Yii::$app->user->canUpdate($this->grid->view->context->resource);
                if ($this->_isDropdown) {
                    $options['tabindex'] = '-1';
                    $link = $visible ? Html::a($label, $url, $options) : '';
                    $li = !empty($link) ? '<li>' . $link . '</li>' . PHP_EOL : '';
                    return $li;
                } else {
                    return $visible ? Html::a($label, $url, $options) : '';
                }
            };
        }
        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model) {
                /* @var $model ActiveRecord */
                $options = $this->deleteOptions;
                if (isset($this->deleteOptions['visible']) && $this->deleteOptions['visible'] === false) {
                    return '';
                }
                $title = Lang::t('Delete');
                $icon = '<span class="fa fa-trash text-muted"></span>';
                $label = ArrayHelper::remove($options, 'label', ($this->_isDropdown ? $icon . ' ' . $title : $icon));
                $options = ArrayHelper::merge(
                    [
                        'title' => $title,
                        'data-confirm-message' => Lang::t('DELETE_CONFIRM'),
                        'data-href' => $url,
                        'data-pjax' => '0',
                        'class' => 'grid-update',
                        'data-grid' => $model->getPjaxWidgetId(),
                    ],
                    $options
                );
                $visible = (isset($this->deleteOptions['visible']) && $this->deleteOptions['visible']) || Yii::$app->user->canDelete($this->grid->view->context->resource);
                if ($this->_isDropdown) {
                    $options['tabindex'] = '-1';
                    $link = $visible ? Html::a($label, 'javascript:void(0);', $options) : '';
                    $li = !empty($link) ? '<li>' . $link . '</li>' . PHP_EOL : '';
                    return $li;
                } else {
                    return $visible ? Html::a($label, 'javascript:void(0);', $options) : '';
                }
            };
        }
    }
}