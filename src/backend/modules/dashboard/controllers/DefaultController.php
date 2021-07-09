<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-04-19
 * Time: 12:04 PM
 */

namespace backend\modules\dashboard\controllers;


class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGraph($graphType = null, $dateRange = null)
    {
        return $this->renderPartial('graph/graph', ['graphType' => $graphType, 'dateRange' => $dateRange]);
    }
}