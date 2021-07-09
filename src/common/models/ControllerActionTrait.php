<?php
/**
 * Created by PhpStorm.
 * @author Fred <mconyango@gmail.com>
 * Date: 2018-06-21
 * Time: 17:09
 */

namespace common\models;


use backend\modules\workflow\models\WorkflowTaskInterface;
use common\helpers\Lang;
use common\helpers\Url;
use Yii;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

trait ControllerActionTrait
{
    /**
     * Performs simple ajax save
     * @param string $view
     * @param string $redirect_route
     * @param array $redirect_params
     * @param string|null $success_msg
     * @param bool $forceRedirect
     * @return bool|string
     */
    public function simpleAjaxSaveRenderAjax($view = '_form', $redirect_route = 'index', $redirect_params = [], $success_msg = null, $forceRedirect = false)
    {
        return $this->simpleAjaxSave($view, $redirect_route, $redirect_params, $success_msg, $forceRedirect, true);
    }

    /**
     * Performs simple ajax save
     * @param string $view
     * @param string $redirect_route
     * @param array $redirect_params
     * @param string|null $success_msg
     * @param bool $forceRedirect
     * @param bool $renderAjax
     * @return bool|string
     */
    public function simpleAjaxSave($view = '_form', $redirect_route = 'index', $redirect_params = [], $success_msg = null, $forceRedirect = false, $renderAjax = false)
    {
        if ($this->load(Yii::$app->request->post())) {

            if ($this->validate()) {
                $transaction = Yii::$app->db->beginTransaction();
                try {

                    $workflowId = null;
                    if ($this instanceof WorkflowTaskInterface) {
                        $workflowId = $this->getWorkFlowId();
                    }

                    if (!empty($workflowId) && empty($success_msg)) {
                        $success_msg = Lang::t('The task has been queued for approval.');
                    } elseif (empty($success_msg)) {
                        $success_msg = Lang::t('SUCCESS_MESSAGE');
                    }

                    if (empty($workflowId)) {
                        $this->save(false);
                    } else {
                        $this->sendToWorkflow(['model' => $this]);
                    }
                    $transaction->commit();

                    $primary_key_field = static::getPrimaryKeyColumn();
                    $redirect_url = Url::to(array_merge([$redirect_route, 'id' => $this->{$primary_key_field}], (array)$redirect_params));
                    return Json::encode(['success' => true, 'message' => $success_msg, 'redirectUrl' => Url::getReturnUrl($redirect_url), 'forceRedirect' => $forceRedirect]);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::debug($e->getTrace());
                    return Json::encode(['success' => false, 'message' => $e->getMessage()]);
                }
            } else {
                return Json::encode(['success' => false, 'message' => $this->getErrors()]);
            }
        }

        if ($renderAjax) {
            return Yii::$app->controller->renderAjax($view, [
                'model' => $this,
            ]);
        }
        return Yii::$app->controller->renderPartial($view, [
            'model' => $this,
        ]);
    }


    /**
     * Performs simple non-ajax save
     * @param string $view
     * @param string $redirect_action
     * @param string|null $success_msg
     * @return mixed
     */
    public function simpleSave($view = 'create', $redirect_action = 'index', $success_msg = null)
    {
        if (empty($success_msg))
            $success_msg = Lang::t('SUCCESS_MESSAGE');

        if ($this->load(Yii::$app->request->post()) && $this->save()) {
            Yii::$app->session->setFlash('success', $success_msg);
            if ($redirect_action === 'index' || $redirect_action === 'create') {
                $redirect_url = Url::to([$redirect_action]);
            } else {
                $primary_key_field = static::getPrimaryKeyColumn();
                $redirect_url = Url::to([$redirect_action, 'id' => $this->{$primary_key_field}]);
            }

            return Yii::$app->controller->redirect(Url::getReturnUrl($redirect_url));
        }

        return Yii::$app->controller->render($view, [
            'model' => $this,
        ]);
    }

    /**
     * @param array $attributes
     * @throws \yii\base\ExitException
     */
    public function ajaxValidate($attributes = [])
    {
        if (Yii::$app->request->isAjax && $this->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = ActiveForm::validate($this, $attributes);
            Yii::$app->end();
        }
    }

    public function workflowCheck()
    {
        $success_msg = null;
        if ($this->load(Yii::$app->request->post()) && $this->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $workflowId = null;
                if ($this instanceof WorkflowTaskInterface) {
                    $workflowId = $this->getWorkFlowId();
                }
                if (!empty($workflowId) && empty($success_msg)) {
                    $success_msg = Lang::t('The task has been queued for approval.');
                } elseif (empty($success_msg)) {
                    $success_msg = Lang::t('SUCCESS_MESSAGE');
                }

                if (empty($workflowId)) {
                    $this->save(false);
                } else {
                    $this->sendToWorkflow(['model' => $this]);
                }
                $transaction->commit();

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error($e->getTrace());
                throw new HttpException(500, $e->getMessage());
            }
        }
        return $success_msg;

    }
}