<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-04-19
 * Time: 11:57 AM
 */

namespace backend\modules\dashboard\controllers;


use backend\controllers\BackendController;
use backend\modules\dashboard\Constants;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class Controller extends BackendController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->activeMenu))
            $this->activeMenu = Constants::MENU_DASHBOARD;
        $this->enableHelpLink = false;

        $this->enableDefaultAcl = false;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'map',
                            'graph',
                            'status',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
}