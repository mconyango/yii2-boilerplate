<?php

namespace backend\modules\auth\controllers;


use backend\controllers\BackendController;
use backend\modules\auth\Constants;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/02
 * Time: 5:43 PM
 */
class Controller extends BackendController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->activeMenu))
            $this->activeMenu = Constants::MENU_USER_MANAGEMENT;
        if (empty($this->resource))
            $this->resource = Constants::RES_USER;

        $this->enableDefaultAcl = true;
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
                            'view',
                            'create',
                            'update',
                            'delete',
                            'upload-image',
                            'change-password',
                            'reset-password',
                            'change-status',
                            'filter',
                            'get-list',
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
                    'change-status' => ['post'],
                ],
            ],
        ];
    }
}