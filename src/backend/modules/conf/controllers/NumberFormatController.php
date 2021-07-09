<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/07
 * Time: 1:46 AM
 */

namespace backend\modules\conf\controllers;

use backend\modules\auth\Session;
use backend\modules\conf\models\NumberingFormat;

class NumberFormatController extends DevController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->resourceLabel = 'Number Format';
    }

    public function actionIndex($org_id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        $condition = ['org_id' => $org_id];
        $searchModel = NumberingFormat::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'condition' => $condition,
        ]);
        $searchModel->is_active = 1;

        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $model = new NumberingFormat();
        return $model->simpleAjaxSaveRenderAjax();
    }

    public function actionUpdate($id)
    {
        $model = NumberingFormat::loadModel($id);
        return $model->simpleAjaxSaveRenderAjax();
    }

    public function actionDelete($id)
    {
        NumberingFormat::softDelete($id);
    }
}