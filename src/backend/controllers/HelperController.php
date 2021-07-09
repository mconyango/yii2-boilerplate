<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 11:06 PM
 */

namespace backend\controllers;


use common\controllers\Controller;
use common\excel\PHPExcelHelper;
use common\widgets\fineuploader\UploadHandler;
use common\helpers\FileManager;
use common\widgets\gmap\GmapUtils;
use Yii;

class HelperController extends Controller
{

    public function actionUploadFile($excel = false)
    {
        $uploader = new UploadHandler();
        $uploader->inputName = 'qqfile';// matches Fine Uploader's default inputName value by default
        if (Yii::$app->request->isPost) {
            // upload file
            $tmp_dir = FileManager::getTempDir();
            $result = $uploader->handleUpload($tmp_dir);
            if (isset($result['success']) && $result['success'] == true) {
                $file_name = $uploader->getName();
                $uuid = $result['uuid'];
                $result['path'] = $tmp_dir . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $file_name;
                $result['url'] = Yii::$app->request->getBaseUrl() . '/uploads/tmp/' . $uuid . '/' . $file_name;

                if ($excel) {
                    $sheets = PHPExcelHelper::getSheetNames($result['path']);
                    $indexed_sheets = [];
                    foreach ($sheets as $sh) {
                        $indexed_sheets[$sh] = $sh;
                    }
                    $result['sheets'] = $indexed_sheets;
                }

            }
            return json_encode($result);
        }
    }

    public function actionDeleteUpload()
    {
        if (!empty($_POST['qquuid'])) {
            $path = FileManager::getTempDir() . DIRECTORY_SEPARATOR . $_POST['qquuid'];
            FileManager::deleteDir($path);
        }

        return json_encode(['success' => true]);
    }


    public function actionDownloadExcelSample($route)
    {
        $mime_type = 'application/vnd.ms-excel';
        $path = Yii::getAlias('@common/excel/samples/' . $route);
        FileManager::downloadFile($path, null, $mime_type);
    }

    public function actionGmapGeocode($address)
    {
        return json_encode(GmapUtils::geoCode($address));
    }


}