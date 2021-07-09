<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/04
 * Time: 10:56 AM
 */

namespace api\actions;


use Yii;
use yii\rest\Action;
use yii\web\ServerErrorHttpException;

class Delete extends Action
{
    /**
     * Deletes a model.
     * @param mixed $id id of the model to be deleted.
     * @throws ServerErrorHttpException on failure.
     */
    public function run($id)
    {
        /* @var $model \common\models\ActiveRecord */
        $model = $this->findModel($id);

        if ($model->softDelete($id) === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }
}