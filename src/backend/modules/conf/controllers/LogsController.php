<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 24/10/18
 * Time: 22:32
 */

namespace backend\modules\conf\controllers;


use common\helpers\FileManager;
use Yii;

class LogsController extends DevController
{
    public function actionRuntime($scope = null)
    {
        $log_file = '';
        if (empty($scope))
            $scope = 'backend';
        if (isset($_POST['log_file'])) {
            $log_file = $_POST['log_file'];
        }
        if (isset($_POST['scope'])) {
            $scope = $_POST['scope'];
        }

        $base_path = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . '_protected' . DIRECTORY_SEPARATOR . $scope . DIRECTORY_SEPARATOR . 'runtime';
        $base_path = $base_path . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
        $log_files = FileManager::getDirectoryFiles($base_path . '*.log*');
        if (empty($log_file)) {
            if (!empty($log_files)) {
                $arr = $log_files;
                reset($arr);
                $log_file = key($arr);
            } else
                $log_file = $base_path . 'app.log';
        }


        if (isset($_POST['clear'])) {
            if (file_exists($log_file)) {
                @unlink($log_file);
            }

            return $this->redirect(['runtime', 'scope' => $scope]);
        }

        if (isset($_POST['download'])) {
            if (file_exists($log_file)) {
                $char = explode('/', $log_file);
                $name = end($char);
                FileManager::downloadFile($log_file, $name);
            }

            return $this->redirect(['runtime', 'scope' => $scope]);
        }

        return $this->render('runtime', [
            'log_files' => $log_files,
            'log_file' => $log_file,
            'scope' => $scope,
        ]);
    }
}