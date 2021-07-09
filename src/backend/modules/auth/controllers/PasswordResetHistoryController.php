<?php

namespace backend\modules\auth\controllers;

use Yii;
use app\modules\auth\models\PasswordResetHistory;
use app\modules\auth\models\PasswordResetHistorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PasswordResetHistoryController implements the CRUD actions for PasswordResetHistory model.
 */
class PasswordResetHistoryController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all PasswordResetHistory models.
     * @return mixed
     */
    public function actionIndex()
    {
        
    }

    /**
     * Finds the PasswordResetHistory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PasswordResetHistory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PasswordResetHistory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
