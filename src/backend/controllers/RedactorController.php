<?php

namespace backend\controllers;

use common\controllers\Controller;
use common\helpers\FileManager;
use vova07\imperavi\actions\GetAction;
use vova07\imperavi\actions\UploadAction;
use Yii;
use yii\helpers\FileHelper;

/**
 * Class RedactorController
 * Used to upload/fetch images and files using the yii2 redactor
 * Uses this plugin => https://github.com/vova07/yii2-imperavi-widget
 *
 * @package backend\controllers
 */
class RedactorController extends Controller
{
    public function init()
    {
        $this->setup();
        parent::init();
    }

    /**
     * Get the absolute url to the redactor paths, ie images and files
     *
     * @param $path
     * @return string
     */
    public function getPathUrl($path)
    {
        return Yii::$app->urlManager->createAbsoluteUrl(['uploads/redactor/' . $path]);
    }

    public function actions()
    {
        return [
            'image-upload' => [
                'class' => UploadAction::class,
                'url' => $this->getPathUrl('images'),
                'path' => '@redactorImages',
                'validatorOptions' => [
                    'extensions' => 'png, jpg, gif',
                    // 3MB
                    'maxSize' => 1024 * 1024 * 3
                ]
            ],
            'file-upload' => [
                'class' => UploadAction::class,
                'path' => '@redactorFiles',
                'url' => $this->getPathUrl('files'),
                'uploadOnlyImage' => false,
                'validatorOptions' => [
                    'extensions' => 'pdf, ppt, xls, docx',
                    // 3MB
                    'maxSize' => 1024 * 1024 * 3
                ]
            ],
            'fetch-files' => [
                'class' => GetAction::class,
                'url' => $this->getPathUrl('files'),
                'path' => '@redactorFiles',
                'type' => GetAction::TYPE_FILES,
            ],
            'fetch-images' => [
                'class' => GetAction::class,
                'url' => $this->getPathUrl('images'),
                'path' => '@redactorImages',
                'type' => GetAction::TYPE_IMAGES,
            ]
        ];
    }

    /**
     * Check if the folders exist, and attempt to create them if they don't
     *
     * @throws \yii\base\Exception
     */
    private function setup()
    {
        // uploads/redactor/
        $root = FileManager::getUploadsDir() . DIRECTORY_SEPARATOR . 'redactor' . DIRECTORY_SEPARATOR;
        $p1 = Yii::getAlias('@redactorImages', false);
        $p2 = Yii::getAlias('@redactorFiles', false);
        if ($p1 === false) {
            FileHelper::createDirectory($root . 'images');
        }
        if ($p2 === false) {
            FileHelper::createDirectory($root . 'files');
        }
    }

}