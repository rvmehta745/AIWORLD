<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\V1\Admin\LoginController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\CommonController;
use App\Http\Controllers\V1\CmsPageController;
use App\Http\Controllers\V1\RoleController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/', function () {
    echo 'Welcome to our AI World - API';
});

Route::group(['middleware' => ['api']], function () {

    Route::post('/register', [LoginController::class, 'register']);
    Route::post('/login', [LoginController::class, 'authenticate']);
    Route::post('/forgot-password', [LoginController::class, 'forgotPasswordOtp']);
    Route::post('/verify-otp', [LoginController::class, 'verifyOtp']);
    Route::post('/reset-password', [LoginController::class, 'resetPasswordViaLink']);
    Route::post('/verify-email', [LoginController::class, 'verifyEmail']);

    // CMS Pages routes
    //   Route::get('/cms-page/{page}', [CmsPageController::class, 'getByPageName']);
    Route::get('/cms-page/{slug}', [CmsPageController::class, 'getBySlug']);
    Route::get('/cms-pages', [CmsPageController::class, 'getAllActive']);


    Route::group(['middleware' => ['authenticateUser']], function () {
        Route::post('/command', [CommonController::class, 'callManuallCommand']);
        Route::post('/change-password', [LoginController::class, 'changePassword']);
        Route::get('/me', [LoginController::class, 'me']);
        Route::post('/update-profile', [LoginController::class, 'updateProfile']);
        Route::get('/logout', [LoginController::class, 'logout']);

        Route::get('/view-log', [CommonController::class, 'viewLog']);
        Route::get('/api-logs', [CommonController::class, 'viewApiLogs']);

        // Admin User routes
        Route::group(['prefix' => 'admin-users'], function () {
            Route::post('/', [UserController::class, 'index'])->middleware('checkUserPermission:ADMIN_USER_MANAGEMENT_INDEX');
            Route::post('/create', [UserController::class, 'store'])->middleware('checkUserPermission:ADMIN_USER_MANAGEMENT_CREATE');
            Route::get('{id}/details', [UserController::class, 'show'])->middleware('checkUserPermission:ADMIN_USER_MANAGEMENT_DETAILS');
            Route::post('{id}/update', [UserController::class, 'update'])->middleware('checkUserPermission:ADMIN_USER_MANAGEMENT_UPDATE');
            Route::delete('{id}/delete', [UserController::class, 'destroy'])->middleware('checkUserPermission:ADMIN_USER_MANAGEMENT_DELETE');
            Route::post('{id}/change-status', [UserController::class, 'changeStatus'])->middleware('checkUserPermission:ADMIN_USER_MANAGEMENT_CHANGE_STATUS');
            Route::get('/active/list', [UserController::class, 'getActiveUsers'])->middleware('checkUserPermission:ADMIN_USER_MANAGEMENT_INDEX');
        });
        Route::group(['prefix' => 'roles'], function () {
            Route::post('/', [RoleController::class, 'index'])->middleware('checkUserPermission:ROLE_MANAGEMENT_INDEX');
            Route::post('/create', [RoleController::class, 'store'])->middleware('checkUserPermission:ROLE_MANAGEMENT_CREATE');
            Route::get('{id}/details', [RoleController::class, 'show'])->middleware('checkUserPermission:ROLE_MANAGEMENT_DETAILS');
            Route::get('{slug}', [RoleController::class, 'getBySlug'])->middleware('checkUserPermission:ROLE_MANAGEMENT_DETAILS');
            Route::post('{id}/update', [RoleController::class, 'update'])->middleware('checkUserPermission:ROLE_MANAGEMENT_UPDATE');
            Route::delete('{id}/delete', [RoleController::class, 'destroy'])->middleware('checkUserPermission:ROLE_MANAGEMENT_DELETE');
            Route::post('{id}/change-status', [RoleController::class, 'changeStatus'])->middleware('checkUserPermission:ROLE_MANAGEMENT_CHANGE_STATUS');
            Route::get('/active/list', [RoleController::class, 'getActiveRoles'])->middleware('checkUserPermission:ROLE_MANAGEMENT_INDEX');
        });
    });

    Route::group(['prefix' => ''], function () {
        Route::post('/sync', [CommonController::class, 'sync']);
        Route::get('/language/{local_key}', [CommonController::class, 'languageTranslationData']);
        Route::get('/privileges-list', [CommonController::class, 'privilegesList']);
        Route::get('/roles-list', [CommonController::class, 'rolesList']);
    });
});
