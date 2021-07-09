<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/07
 * Time: 3:01 AM
 */

namespace backend\modules\conf\controllers;

use backend\modules\conf\models\JobProcesses;
use backend\modules\conf\models\Jobs;

class JobManagerController extends DevController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->resourceLabel = 'Job';
    }

    public function actionIndex()
    {
        $searchModel = Jobs::searchModel(['defaultOrder' => ['id' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionView($id)
    {
        $job = Jobs::loadModel($id);

        return $this->render('view', [
            'searchModel' => JobProcesses::searchModel(['condition' => ['job_id' => $id], 'defaultOrder' => ['created_at' => SORT_DESC]]),
            'job' => $job,
        ]);
    }

    public function actionCreate()
    {
        $model = new Jobs();
        return $model->simpleAjaxSaveRenderAjax();
    }

    public function actionUpdate($id)
    {
        $model = Jobs::loadModel($id);
        return $model->simpleAjaxSaveRenderAjax();
    }

    public function actionDelete($id)
    {
        Jobs::softDelete($id);
    }

    public function actionStart($id)
    {
        Jobs::startJob($id);
    }

    public function actionStop($id)
    {
        Jobs::stopJob($id);
    }
}