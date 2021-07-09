<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:57 PM
 */

namespace api\controllers;

class ActiveController extends \yii\rest\ActiveController
{
    public $serializer = [
        'class' => \yii\rest\Serializer::class,
        'expandParam' => 'include',
    ];

    /**
     * Define this in each controller to enable ACL
     * @var string
     */
    protected $resource;

    protected function defaultActions()
    {
        return [
            'index' => [
                'class' => \api\actions\Index::class,
                'modelClass' => $this->modelClass,
            ],
            'view' => [
                'class' => \api\actions\View::class,
                'modelClass' => $this->modelClass,
            ],
            'create' => [
                'class' => \api\actions\Create::class,
                'modelClass' => $this->modelClass,
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => \api\actions\Update::class,
                'modelClass' => $this->modelClass,
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => \api\actions\Delete::class,
                'modelClass' => $this->modelClass,
            ],
            'options' => [
                'class' => \yii\rest\OptionsAction::class,
            ],
        ];
    }

    public function actions()
    {
        $actions = $this->defaultActions();
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
}