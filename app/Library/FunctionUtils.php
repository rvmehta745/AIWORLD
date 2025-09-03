<?php

namespace App\Library;

use App\Traits\CommonTrait;

class FunctionUtils
{
    private static string $s3Url;

    public static function createDir($dirName)
    {
        if (!empty($dirName)) {
            $UpDir = CommonTrait::getUploadPath();
            if (!is_dir($UpDir)) {
                mkdir($UpDir);       //create the directory
                chmod($UpDir, 0777); //make it writable
            }

            $cacheDir = CommonTrait::getUploadPath() . "cache/";
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir);       //create the directory
                chmod($cacheDir, 0777); //make it writable
            }

            $path = CommonTrait::getUploadPath() . $dirName . "/";
            if (!is_dir($path)) {
                mkdir($path, 0777);
                chmod($path, 0777);
            }
        }
    }

    public static function imageType($Files)
    {
        if ($Files->isValid()) {
            $extension         = $Files->getClientOriginalExtension(); // getting image extension
            $ValidExtensionArr = array("jpg", "jpeg", "png", "gif");
            if (in_array(strtolower($extension), $ValidExtensionArr)) {
                if (in_array(strtolower($extension), $ValidExtensionArr)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    // FUNCTION TO UPLOAD FILE OF SPECIFIED EXT. ---
    public static function uploadFile($files, $destPath, $alreadyExits = '', $extra = '')
    {
        if (!empty($alreadyExits)) {
            $file_path = $destPath . '/' . $alreadyExits;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        if (!empty($files) && $files->isValid()) {
            $extra             = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
            $extension         = $files->getClientOriginalExtension(); // getting image extension
            $ValidExtensionArr = array("jpg", "jpeg", "png", "gif");
            if (!empty($extra)) {
                $fileName = time() . "_" . $extra . '.' . $extension;
            } else {
                $fileName = time() . '.' . $extension;
            }
            $files->move($destPath, $fileName); // uploading file to given path
            return $fileName;
        } else {
            return false;
        }
    }

    // FUNCTION TO UPLOAD FILE OF SPECIFIED EXT. ---
    public static function getUploadFileName()
    {
        $extra = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);

        while (true) {
            if (!empty($extra)) {
                $fileName = time() . "_" . $extra;
            } else {
                $fileName = time() . "_" . $extra;
            }
        }
        return $fileName;
    }

    public static function getFileName($Files, $extra = '')
    {
        $FileName = '';
        if ($Files->isValid()) {
            $extra     = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
            $extension = $Files->getClientOriginalExtension(); // getting image extension
            if (!empty($extra)) {
                $FileName = time() . "_" . $extra . '.' . $extension;
            } else {
                $FileName = time() . '.' . $extension;
            }
            return $FileName;
        } else {
            return false;
        }
    }

    public static function getCouponCode()
    {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $res   = "";
        while (true) {
            for ($i = 0; $i < 8; $i++) {
                $res .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            $exit = Promotion::where("code", "=", $res)->count();
            if ($exit == 0) {
                break;
            }
        }
        return $res;
    }


    public static function getAdultDate()
    {
        return date("Y-m-d");
    }

    public static function getPageSlug($Title, $divider = '-')
    {
        // replace non letter or digits by divider
        $Title = preg_replace('~[^\pL\d]+~u', $divider, $Title);
        // transliterate
        $Title = iconv('utf-8', 'us-ascii//TRANSLIT', $Title);
        // remove unwanted characters
        $Title = preg_replace('~[^-\w]+~', '', $Title);
        // trim
        $Title = trim($Title, $divider);
        // remove duplicate divider
        $Title = preg_replace('~-+~', $divider, $Title);
        // lowercase
        $Title = strtolower($Title);
        if (empty($Title)) {
            return 'n-a';
        }
        return $Title;
    }

    public static function setSettingKey($key)
    {
        return strtolower($key);
    }

    // FUNCTION TO UPLOAD FILE OF SPECIFIED EXT. ---
    public static function uploadFileOnS3($files, $destPath, $oldFileName = null)
    {
        self::deleteFileOnS3($oldFileName, $destPath);

        if (!empty($files) && $files->isValid()) {
            $extra     = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
            $extension = $files->getClientOriginalExtension();
            if (!empty($extra)) {
                $fileName = time() . "_" . $extra . '.' . $extension;
            } else {
                $fileName = time() . '.' . $extension;
            }

            $response = \Storage::disk('s3')->putFileAs($destPath, $files, $fileName);
            return $response ? $fileName : false;
        } else {
            return false;
        }
    }

    // FUNCTION TO DELETE FILE
    public static function deleteFileOnS3($oldFileName, $destPath = '')
    {
        if (!empty($oldFileName) && \Storage::disk('s3')->exists($destPath . $oldFileName)) {
            return \Storage::disk('s3')->delete($destPath . $oldFileName);
        }
        return false;
    }

    /**
     * Set s3 URL
     */
    public static function setS3Url()
    {
        self::$s3Url = substr_replace(\Storage::disk('s3')->url('/temp'), "", -4);
    }

    /**
     * Get s3 URL
     * @param string $fileName
     * @return string
     */
    public static function getS3Url($fileName = null)
    {
        if (empty(self::$s3Url)) {
            self::setS3Url();
        }
        return !empty($fileName) ? self::$s3Url . $fileName : self::$s3Url;
    }

    /**
     * Download folder file
     * @param        $fileName
     * @param string $destPath
     * @param string $accessType
     * @return string
     */
    public static function getS3FileUrl($fileName, $destPath = '', $accessType = 'public')
    {
        if ($accessType == 'public') {
            return \Storage::disk('s3')->url($destPath . $fileName);
        } else {
            // Get the actual presigned-url
            return \Storage::disk('s3')->temporaryUrl($destPath . $fileName, now()->addMinutes(60));
        }
    }
}
