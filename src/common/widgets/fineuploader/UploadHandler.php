<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/06/15
 * Time: 4:08 PM
 */

namespace common\widgets\fineuploader;


use common\helpers\FileManager;
use common\helpers\Utils;

class UploadHandler
{
    public $allowedExtensions = [];
    public $sizeLimit = null;
    public $inputName = 'qqfile';
    public $chunksFolder;

    public $chunksCleanupProbability = 0.001; // Once in 1000 requests on avg
    public $chunksExpireIn = 604800; // One week

    protected $uploadName;

    function __construct()
    {
        $this->sizeLimit = $this->toBytes(ini_get('upload_max_filesize'));
        $this->chunksFolder = FileManager::getTempDir() . DIRECTORY_SEPARATOR . 'fineUploaderChunks';
    }

    /**
     * Get the original filename
     */
    public function getName()
    {
        if (isset($_REQUEST['qqfilename']))
            return $_REQUEST['qqfilename'];

        if (isset($_FILES[$this->inputName]))
            return $_FILES[$this->inputName]['name'];
    }

    /**
     * Get the name of the uploaded file
     */
    public function getUploadName()
    {
        return $this->uploadName;
    }

    /**
     * Process the upload.
     * @param string $uploadDirectory Target directory.
     * @param string $name Overwrites the name of the file.
     * @return array
     */
    public function handleUpload($uploadDirectory, $name = null)
    {

        if (is_writable($this->chunksFolder) &&
            1 == mt_rand(1, 1 / $this->chunksCleanupProbability)
        ) {

            // Run garbage collection
            $this->cleanupChunks();
        }

        // Check that the max upload size specified in class configuration does not
        // exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit ||
            $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit
        ) {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            return ['error' => "Server error. Increase post_max_size and upload_max_filesize to " . $size];
        }

        if ($this->isInaccessible($uploadDirectory)) {
            return ['error' => "Server error. Uploads directory isn't writable"];
        }

        if (!isset($_SERVER['CONTENT_TYPE'])) {
            return ['error' => "No files were uploaded."];
        } elseif (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'multipart/') !== 0) {
            return ['error' => "Server error. Not a multipart request. Please set forceMultipart to default value (true)."];
        }

        // Get size and name
        $file = $_FILES[$this->inputName];
        $size = $file['size'];

        if ($name === null) {
            $name = $this->getName();
        }

        // Validate name
        if ($name === null || $name === '') {
            return ['error' => 'File name empty.'];
        }

        // Validate file size
        if ($size == 0) {
            return ['error' => 'File is empty.'];
        }

        if ($size > $this->sizeLimit) {
            return ['error' => 'File is too large.'];
        }

        // Validate file extension
        $pathinfo = pathinfo($name);
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

        if ($this->allowedExtensions && !in_array(strtolower($ext), array_map("strtolower", $this->allowedExtensions))) {
            $these = implode(', ', $this->allowedExtensions);
            return ['error' => 'File has an invalid extension, it should be one of ' . $these . '.'];
        }

        // Save a chunk
        $totalParts = isset($_REQUEST['qqtotalparts']) ? (int)$_REQUEST['qqtotalparts'] : 1;

        // generate uuid if absent in request, this is useful when using this class for API uploads.
        $uuid = $_REQUEST['qquuid'] ?? Utils::uuid();
        if ($totalParts > 1) {
            # chunked upload

            $chunksFolder = $this->chunksFolder;
            $partIndex = (int)$_REQUEST['qqpartindex'];

            if (!is_writable($chunksFolder) && !is_executable($uploadDirectory)) {
                return ['error' => "Server error. Chunks directory isn't writable or executable."];
            }

            $targetFolder = $this->chunksFolder . DIRECTORY_SEPARATOR . $uuid;

            if (!file_exists($targetFolder)) {
                mkdir($targetFolder);
            }

            $target = $targetFolder . '/' . $partIndex;
            $success = move_uploaded_file($_FILES[$this->inputName]['tmp_name'], $target);

            // Last chunk saved successfully
            if ($success AND ($totalParts - 1 == $partIndex)) {

                $target = join(DIRECTORY_SEPARATOR, [$uploadDirectory, $uuid, $name]);
                //$target = $this->getUniqueTargetPath($uploadDirectory, $name);
                $this->uploadName = $uuid . DIRECTORY_SEPARATOR . $name;

                if (!file_exists($target)) {
                    mkdir(dirname($target));
                }
                $target = fopen($target, 'wb');

                for ($i = 0; $i < $totalParts; $i++) {
                    $chunk = fopen($targetFolder . DIRECTORY_SEPARATOR . $i, "rb");
                    stream_copy_to_stream($chunk, $target);
                    fclose($chunk);
                }

                // Success
                fclose($target);

                for ($i = 0; $i < $totalParts; $i++) {
                    unlink($targetFolder . DIRECTORY_SEPARATOR . $i);
                }

                rmdir($targetFolder);

                return ["success" => true, "uuid" => $uuid];
            }

            return ["success" => true, "uuid" => $uuid];

        } else {
            # non-chunked upload

            $target = join(DIRECTORY_SEPARATOR, [$uploadDirectory, $uuid, $name]);
            //$target = $this->getUniqueTargetPath($uploadDirectory, $name);

            if ($target) {
                $this->uploadName = basename($target);

                if (!is_dir(dirname($target))) {
                    mkdir(dirname($target));
                }
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    return ['success' => true, "uuid" => $uuid];
                }
            }

            return ['error' => 'Could not save uploaded file.' . 'The upload was cancelled, or server error encountered'];
        }
    }


    /**
     * Returns a path to use with this upload. Check that the name does not exist,
     * and appends a suffix otherwise.
     * @param string $uploadDirectory Target directory
     * @param string $filename The name of the file to use.
     * @return bool|string
     */
    protected function getUniqueTargetPath($uploadDirectory, $filename)
    {
        // Allow only one process at the time to get a unique file name, otherwise
        // if multiple people would upload a file with the same name at the same time
        // only the latest would be saved.

        if (function_exists('sem_acquire')) {
            $lock = sem_get(ftok(__FILE__, 'u'));
            sem_acquire($lock);
        }

        $pathinfo = pathinfo($filename);
        $base = $pathinfo['filename'];
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
        $ext = $ext == '' ? $ext : '.' . $ext;

        $unique = $base;
        $suffix = 0;

        // Get unique file name for the file, by appending random suffix.

        while (file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext)) {
            $suffix += rand(1, 999);
            $unique = $base . '-' . $suffix;
        }

        $result = $uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext;

        // Create an empty target file
        if (!touch($result)) {
            // Failed
            $result = false;
        }

        if (function_exists('sem_acquire')) {
            sem_release($lock);
        }

        return $result;
    }

    /**
     * Deletes all file parts in the chunks folder for files uploaded
     * more than chunksExpireIn seconds ago
     */
    protected function cleanupChunks()
    {
        foreach (scandir($this->chunksFolder) as $item) {
            if ($item == "." || $item == "..")
                continue;

            $path = $this->chunksFolder . DIRECTORY_SEPARATOR . $item;

            if (!is_dir($path))
                continue;

            if (time() - filemtime($path) > $this->chunksExpireIn) {
                FileManager::deleteDir($path);
            }
        }
    }

    /**
     * Converts a given size with units to bytes.
     * @param string $str
     * @return int|string
     */
    protected function toBytes($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        $val = (float)$val;
        switch ($last) {
            case 'g':
                $val *= pow(1024, 3);
                break;
            case 'm':
                $val *= pow(1024, 2);
                break;
            case 'k':
                $val *= 1024;
                break;
        }
        return $val;
    }

    /**
     * Determines whether a directory can be accessed.
     *
     * is_writable() is not reliable on Windows
     *  (http://www.php.net/manual/en/function.is-executable.php#111146)
     * The following tests if the current OS is Windows and if so, merely
     * checks if the folder is writable;
     * otherwise, it checks additionally for executable status (like before).
     *
     * @param string $directory The target directory to test access
     * @return bool
     */
    protected function isInaccessible($directory)
    {
        $isWin = static::isWin();
        $folderInaccessible = ($isWin) ? !is_writable($directory) : (!is_writable($directory) && !is_executable($directory));
        return $folderInaccessible;
    }

    public static function isWin()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }
}