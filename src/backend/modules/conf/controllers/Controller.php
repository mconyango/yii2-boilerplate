<?php

namespace backend\modules\conf\controllers;

use backend\controllers\BackendController;
use backend\modules\conf\Constants;
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
            $this->activeMenu = Constants::MENU_SETTINGS;
        if (empty($this->resource))
            $this->resource = Constants::RES_SETTINGS;
        if (empty($this->helpModuleName)) {
            $this->helpModuleName = 'User Management';
        }

        $this->enableDefaultAcl = true;
    }

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
                            'settings',
                            'runtime',
                            'start',
                            'stop',
                            'fetch',
                            'mark-as-read',
                            'mark-as-seen',
                            'download',
                            'upload',
                            'password',
                            'resend',
                            'test',
                            'get-list',
                            'get-area-list',
                            'google-map',
                            'quick-create',
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