<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-06-12 10:52 PM
 */

namespace common\widgets\lineItem;


interface LineItemModelInterface
{
    /**
     * @param int $count
     * @param array $config
     * @param mixed $condition
     * @param array $params
     * @return mixed
     */
    public static function getLineModels($count, $config = [], $condition = null, $params = []);

    /**
     * Sample return value
     * ```php
     * return $fields = [
     *    ['attribute'=>'name','type'=>LineItem::LINE_ITEM_FIELD_TYPE_TEXT_INPUT,'options'=>['class'=>'form-control'],'tdOptions'=>[]],
     *    ['attribute'=>'category_id','type'=>LineItem::LINE_ITEM_FIELD_TYPE_DROP_DOWN_LIST,'listItems'=>Category::getListData(),'options'=>['class'=>'form-control']],
     *    ['attribute'=>'price','type'=>LineItem::LINE_ITEM_FIELD_TYPE_TEXT_INPUT,'options'=>['class'=>'form-control'],'template'=>"<div class='input-group'><span class='input-group-addon'>USD</span>{input}</div>"],
     * ];
     * ```
     * @return array
     */
    public function lineItemFields();

    /**
     * Sample return value
     * ```php
     * $labels = [
     *    ['label'=>'Name','options'=>['class'=>'text-bold']],
     *    ['label'=>'Description','options'=>['class'=>'text-bold']],
     * ];
     * //OR
     * $labels = [
     *    'Name',
     *    'Description',
     * ];
     * ```
     * @return array
     */
    public function lineItemFieldsLabels();
}