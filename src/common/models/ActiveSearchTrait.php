<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/01
 * Time: 1:56 PM
 */

namespace common\models;

use backend\modules\conf\settings\SystemSettings;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

trait ActiveSearchTrait
{

    /**
     *
     * ```php
     *   [
     *     'defaultOrder'=>['name'=>SORT_ASC],
     *     'condition'=>"",// string or array
     *     'params'=>[],
     *     'pageSize'=>30,
     *     'enablePagination'=>true,
     *     'with'=>[],
     *     'asArray'=>false,
     *   ]
     * ```
     * @var array
     */
    private $_searchOptions;

    /**
     * @var string
     */
    public $_dateFilterFrom;
    /**
     * @var string
     */
    public $_dateFilterTo;

    /**
     * Creates data provider instance with search query applied
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        /* @var $this ActiveRecord|ActiveSearchInterface */
        $defaultOrder = ArrayHelper::getValue($this->_searchOptions, 'defaultOrder', []);
        $condition = ArrayHelper::getValue($this->_searchOptions, 'condition', '');
        $params = ArrayHelper::getValue($this->_searchOptions, 'params', []);
        $pageSize = ArrayHelper::getValue($this->_searchOptions, 'pageSize', SystemSettings::getPaginationSize());
        $enablePagination = ArrayHelper::getValue($this->_searchOptions, 'enablePagination', true);
        $with = ArrayHelper::getValue($this->_searchOptions, 'with');
        $joinWith = ArrayHelper::getValue($this->_searchOptions, 'joinWith');
        $asArray = ArrayHelper::getValue($this->_searchOptions, 'asArray');
        $query = $this->find();
        if ($asArray === true) {
            $query->asArray();
        }
        if ($with !== null) {
            $query->with($with);
        }
        if ($joinWith !== null) {
            $query->joinWith($joinWith);
        }
        /** @var $query ActiveQuery */
        $query->andWhere($condition);
        $query->addParams($params);
        if ($enablePagination) {
            $pagination = [
                'pageSize' => $pageSize,
            ];
        } else {
            $pagination = false;
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => $pagination,
            'sort' => new Sort([
                'defaultOrder' => $defaultOrder,
            ])
        ]);

        $this->load(Yii::$app->request->queryParams);

        foreach ($this->searchParams() as $filter) {
            if (!is_array($filter)) {
                $query->andFilterWhere([$filter => $this->{$filter}]);
            } else {
                $operator = !empty($filter[3]) ? $filter[3] : 'like';
                if (!empty($filter[2]) && strtolower($filter[2]) === 'or') {
                    $query->orFilterWhere([$operator, $filter[0], $this->{$filter[1]}]);

                } else {
                    $query->andFilterWhere([$operator, $filter[0], $this->{$filter[1]}]);
                }
            }
        }

        return $dataProvider;
    }

    /**
     * ```php
     *   [
     *     'defaultOrder'=>['name'=>SORT_ASC],
     *     'condition'=>"",// string or array
     *     'params'=>[],
     *     'pageSize'=>30,
     *     'enablePagination'=>true,
     *     'with'=>[],
     *     'asArray'=>false,
     *   ]
     * ```
     * @param array $options
     *
     * @return $this $model
     */
    public static function searchModel(array $options)
    {
        $class_name = static::class;
        $model = new $class_name(['scenario' => ActiveRecord::SCENARIO_SEARCH]);
        $model->_searchOptions = $options;

        return $model;
    }
}