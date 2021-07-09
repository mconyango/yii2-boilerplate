<?php

namespace api\modules\v1\controllers;

use api\controllers\AuthTrait;
use api\controllers\SendsResponse;
use api\modules\v1\DataArraySerializer;
use League\Fractal\Manager;
use Yii;

class ActiveController extends \api\controllers\ActiveController
{
    use AuthTrait, SendsResponse;

    public function init()
    {
        $this->manager = new Manager();
        $this->manager->setSerializer(new DataArraySerializer());
        parent::init();
    }

    public function actions()
    {
        $actions = parent::actions();
        foreach (array_merge($this->actionsToOverride(), $this->actionsToHide()) as $item) {
            unset($actions[$item]);
        }
        return $actions;
    }

    /**
     * Actions that should be overridden
     * By default, we override all.
     * If a custom implementation isn't supplied, a 404 error will be triggered
     *
     * @return array
     */
    protected function actionsToOverride()
    {
        return ['index', 'create', 'update', 'delete', 'view'];
    }

    /**
     * Actions that should be hidden. A 404 will be triggered if the actions are triggered
     * @return array
     */
    protected function actionsToHide()
    {
        return [];
    }

    /**
     * Get relations from a query param
     *
     * @return array|mixed
     */
    protected function getRelations()
    {
        $this->manager->parseIncludes(Yii::$app->request->get());
        $inc = Yii::$app->request->get('include');
        if (!empty($inc)) {
            if (str_contains($inc, ',')) {
                $with = explode(',', $inc);
            } else {
                $with = $inc;
            }
        } else {
            // passing an empty array to with() does nothing
            $with = [];
        }
        return $with;
    }
}