<?php

namespace App\Library;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class General
{

    public static string $siteName = 'Project Name';
    public static string $siteEmail = 'info@projectName.com';

    /* Global validation */
    public static int    $nameMin = 2;
    public static int    $nameMax = 50;
    public static int    $firstnameMin = 2;
    public static int    $firstnameMax = 50;
    
    public static int    $lastnameMin = 2;
    public static int    $lastnameMax = 50;

    public static int    $emailMin = 3;
    public static int    $emailMax = 70;
    public static int    $descriptionMin = 3;
    public static int    $descriptionMax = 200;
    public static int    $passwordMin = 8;
    public static int    $passwordMax = 20;
    public static string $passwordRegex = "/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%@&!\"_',\}\{.\]\[<>?\~\`;:\/+\-\\*]).*$/";
    public static int    $imageSize = 1024;
    public static string $imageMimes = "jpg,jpeg,png,gif,heif,heic";
    public static string $imageAccept = "image/jpg,image/jpeg,image/png,image/gif,image/heif,image/heic";
    public static int    $videoAcceptSize = 5120;
    public static string $videoMimes = "flv,mp4,3gpp";
    public static string $videoAcceptType = "video/x-flv,video/mp4,video/3gpp";
    public static int    $mobMin = 4;
    public static int    $mobMax = 20;
    public static string $mobRegex = "/^[0-9]+$/";

    /**
     *
     * @param       $type
     * @param array | string $message
     * @param array $result
     * @return JsonResponse
     */
    public static function setResponse($type, $message = [], $result = [])
    {
        $message = gettype($message) === "string" ? [$message] : $message;
        $default_message = [];
        $code = null;

        switch (strtoupper($type)) {
            case 'SUCCESS':
                $code = 200;
                $default_message['NOTIFICATION'] = $message;
                break;
//            case 'INVALID_API_KEY':
//                $code = 403;
//                $default_message['NOTIFICATION'] = [__('api.notifications.INVALID_API_KEY')];
//                break;
//            case 'INVALID_PLATFORM':
//                $code = 403;
//                $default_message['NOTIFICATION'] = [__('api.notifications.INVALID_PLATFORM')];
//                break;
//            case 'INVALID_APP_ID':
//                $code = 403;
//                $default_message['NOTIFICATION'] = [__('api.notifications.INVALID_APP_ID')];
//                break;
//            case 'DEVICE_NOT_AUTHORIZED':
//                $code = 403;
//                $default_message['NOTIFICATION'] = [__('api.notifications.DEVICE_NOT_AUTHORIZED')];
//                break;
            case 'UNAUTHORIZED_LOGIN':
                $code = 423;
                $default_message['NOTIFICATION'] = [__('messages.unauthorized_login')];
                break;
            case 'SESSION_EXPIRED':
                $code = 401;
                $default_message['NOTIFICATION'] = [__('messages.your_session_has_been_expired_kindly_login_again')];
                break;
            case 'NOT_LOGGED_IN':
                $code = 401;
                $default_message['NOTIFICATION'] = [__('messages.you_are_not_logged_in')];
                break;
            case 'USER_DEACTIVATED':
                $code = 512;
                $default_message['NOTIFICATION'] = [__('messages.your_account_is_in_active_please_contact_admin')];
                break;
//            case 'USER_ARCHIVED':
//                $code = 513;
//                $default_message['NOTIFICATION'] = [__('api.notifications.USER_ARCHIVED')];
//                break;
//            case 'LICENSE_EXPIRED':
//                $code = 514;
//                $default_message['NOTIFICATION'] = [__('api.notifications.LICENSE_EXPIRED')];
//                break;
            case 'VALIDATION_ERROR':
                $code = 422;
                $default_message = $message;
                break;
            case 'OTHER_ERROR':
                $code = 423;
                $default_message['NOTIFICATION'] = $message;
                break;
            case 'INVALID_URL':
                $code = 404;
                $default_message['NOTIFICATION'] = [__('messages.invalid_url')];
                break;
            case 'INVALID_USER':
                $code = 401;
                $default_message['NOTIFICATION'] = [__('messages.invalid_user')];
                break;
            case 'NO_PERMISSION':
                $code = 423;
                $default_message['NOTIFICATION'] = [__('messages.you_do_not_have_the_permission_to_use_this_resource')];
                break;
            case 'EXCEPTION':
                $code = 500;
                if (env('APP_ENV') != 'local' || env('APP_DEBUG') != true) {
                    $message = "Something went wrong.";
                }
                $default_message['NOTIFICATION'] = $message;
                break;
            default:
                break;
        }

        $data = [];
        $data['STATUS'] = $default_message;
        if (!empty($result)) {
            $data = array_merge($data, $result);
        }

        return response()->json($data, $code);
    }
}
