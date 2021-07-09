<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 11:10 PM
 */

namespace common\helpers;


use Yii;

class FileManager
{
    /**
     * Deletes all temp files in the temp folder
     * more than chunksExpireIn seconds ago
     * @param int $expiry_secs default is 3600sec (1 hr)
     */
    public static function clearTempFiles($expiry_secs = 3600)
    {
        $temp_dir = static::getTempDir();
        foreach (scandir($temp_dir) as $item) {
            if ($item == "." || $item == "..")
                continue;
            $path = $temp_dir . DIRECTORY_SEPARATOR . $item;

            if (is_file($path) && (time() - filemtime($path) >= $expiry_secs))
                @unlink($path);

            elseif (is_dir($path) && (time() - filemtime($path) >= $expiry_secs)) {
                static::deleteDir($path);
            }
        }
    }

    /**
     * Get the app temp dir (for storing temporary files during uploads)
     * @return string
     */
    public static function getTempDir()
    {
        return static::createDir(static::getUploadsDir() . DIRECTORY_SEPARATOR . TEMP_DIR);
    }

    /**
     * Creates a new directory
     * @param string $dir_name
     * @param integer $permission
     * @return string $dir_name
     */
    public static function createDir($dir_name, $permission = 0755)
    {
        //check if the directory already exists
        if (!is_dir($dir_name)) {
            //create the user's root dir
            mkdir($dir_name, $permission);
        }
        return $dir_name;
    }

    /**
     * Get the uploads directory
     * @return string
     */
    public static function getUploadsDir()
    {
        return Yii::getAlias('@uploads');
    }

    /**
     * Deletes a directory and its contents
     * @param string $path path to the file/folder
     * @return bool
     */
    public static function deleteDir($path)
    {
        if (!file_exists($path))
            return FALSE;

        $this_func = [__CLASS__, __FUNCTION__];
        return is_file($path) ? @unlink($path) : array_map($this_func, glob($path . '/*')) == @rmdir($path);
    }

    /**
     *
     * @param string $file_path
     * @param string $download_name
     * @param string $mime_type
     * @throws \yii\base\ExitException
     */
    public static function downloadFile($file_path, $download_name = null, $mime_type = null)
    {
        if (file_exists($file_path)) {
            if (empty($download_name))
                $download_name = basename($file_path);
            if (empty($mime_type)) {
                $mime_type = mime_content_type($file_path);
            }
            $content_type = !empty($mime_type) ? $mime_type . ', application/octet-stream' : 'application/octet-stream';
            header('Content-Description: File Transfer');
            header("Content-Type: {$content_type}");
            header('Content-Disposition: attachment; filename=' . $download_name);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            ob_clean();
            flush();
            readfile($file_path);
            exit();
        }
    }

    /**
     * Unzip a zipped file
     * @param string $file_path
     * @param string $extract_to
     * @return bool
     */
    public static function unzip($file_path, $extract_to)
    {
        $zip = new \ZipArchive;
        $res = $zip->open($file_path);
        if ($res === TRUE) {
            $zip->extractTo($extract_to . '/');
            $zip->close();
            return true;
        }
        return false;
    }

    /**
     *  Upload an image
     * @param string $basePath
     * @param string $file_element_name
     * @param string $new_file_name
     * @param array $allowed_ext
     * @param int $max_size
     * @param array|string $allowed_types
     * @return array
     */
    public static function uploadImage($basePath, $file_element_name = 'file', $new_file_name = null, $allowed_ext = ['gif', 'jpeg', 'jpg', 'png'], $max_size = 6291456, $allowed_types = ["image/gif", "image/jpg", "image/jpeg", "image/pjpeg", "image/x-png", "image/png"])
    {
        $response = [];
        //validate file types
        if (!in_array($_FILES[$file_element_name]["type"], $allowed_types)) {
            $response['error'] = 'Invalid file type.';
            return $response;
        }
        //validate extension type
        $extension = end(explode(".", $_FILES[$file_element_name]["name"]));
        if (!in_array($extension, $allowed_ext)) {
            $response['error'] = 'Invalid file extension.';
            return $response;
        }

        //validate file size
        if ($_FILES[$file_element_name]["size"] > $max_size) {
            $response['error'] = 'The file is more than the maximum allowed size. The maximum allowed size is ' . ($max_size / (1024 * 1024)) . 'MB.';
            return $response;
        }

        if ($_FILES["file"]["error"] > 0) {
            $response['error'] = $_FILES[$file_element_name]["error"];
            return $response;
        }

        //now upload the file and return the file name
        $file_name = !empty($new_file_name) ? $new_file_name . '.' . $extension : $_FILES[$file_element_name]["name"];
        move_uploaded_file($_FILES[$file_element_name]["tmp_name"], $basePath . DIRECTORY_SEPARATOR . $file_name);
        $response['file_name'] = $file_name;
        $response['file_path'] = $basePath . DIRECTORY_SEPARATOR . $file_name;
        $response['extension'] = $extension;
        $response['success'] = 'File uploaded successfully';
        return $response;
    }

    /**
     * Get directory files in a directory matching a particular pattern
     * @param string $pattern regex pattern
     * @param boolean $return_associative
     * @return array
     */
    public static function getDirectoryFiles($pattern = '*', $return_associative = true)
    {
        $files = [];
        $matches = glob($pattern);
        if (is_array($matches)) {
            foreach (glob($pattern) as $file) {
                $files[] = $file;
            }
        }

        if ($return_associative) {
            $assc = [];
            foreach ($files as $k => $v) {
                $assc[$v] = basename($v);
            }
            return $assc;
        }

        return $files;
    }

    /**
     * Get file extension
     * @param string $path the full path of the file
     * @return string
     */
    public static function getFileExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * @param $file_path
     * @return null|string
     */
    public static function getUrlFromPath($file_path)
    {
        if (empty($file_path)) {
            return null;
        }
        $asset = Yii::$app->getAssetManager()->publish($file_path);

        return $asset[1];
    }
}