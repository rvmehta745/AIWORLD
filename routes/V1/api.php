<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\V1\Admin\LoginController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\CommonController;
use App\Http\Controllers\V1\CmsPageController;
use App\Http\Controllers\V1\RoleController;
use App\Http\Controllers\V1\ProductTypeController;
use App\Http\Controllers\V1\CategoryController;
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

        // User routes
        Route::group(['prefix' => 'users'], function () {
            Route::post('/', [UserController::class, 'index'])->middleware('checkUserPermission:USER_INDEX');
            Route::post('/create', [UserController::class, 'store'])->middleware('checkUserPermission:USER_CREATE');
            Route::get('{id}/details', [UserController::class, 'show'])->middleware('checkUserPermission:USER_DETAILS');
            Route::post('{id}/update', [UserController::class, 'update'])->middleware('checkUserPermission:USER_UPDATE');
            Route::delete('{id}/delete', [UserController::class, 'destroy'])->middleware('checkUserPermission:USER_DELETE');
            Route::post('{id}/change-status', [UserController::class, 'changeStatus'])->middleware('checkUserPermission:USER_CHANGE_STATUS');
            Route::get('/active/list', [UserController::class, 'getActiveUsers'])->middleware('checkUserPermission:USER_INDEX');
        });
        Route::group(['prefix' => 'roles'], function () {
            Route::post('/', [RoleController::class, 'index'])->middleware('checkUserPermission:ROLE_MANAGEMENT_INDEX');
            Route::post('/create', [RoleController::class, 'store'])->middleware('checkUserPermission:ROLE_MANAGEMENT_CREATE');
            Route::get('{id}/details', [RoleController::class, 'show'])->middleware('checkUserPermission:ROLE_MANAGEMENT_DETAILS');
            Route::post('{id}/update', [RoleController::class, 'update'])->middleware('checkUserPermission:ROLE_MANAGEMENT_UPDATE');
            Route::delete('{id}/delete', [RoleController::class, 'destroy'])->middleware('checkUserPermission:ROLE_MANAGEMENT_DELETE');
            Route::post('{id}/change-status', [RoleController::class, 'changeStatus'])->middleware('checkUserPermission:ROLE_MANAGEMENT_CHANGE_STATUS');
            Route::get('/active/list', [RoleController::class, 'getActiveRoles'])->middleware('checkUserPermission:ROLE_MANAGEMENT_INDEX');
        });
        Route::group(['prefix' => 'product-types'], function () {
            Route::post('/', [ProductTypeController::class, 'index'])->middleware('checkUserPermission:PRODUCT_TYPES_INDEX');
            Route::post('/create', [ProductTypeController::class, 'store'])->middleware('checkUserPermission:PRODUCT_TYPES_CREATE');
            Route::get('{id}/details', [ProductTypeController::class, 'show'])->middleware('checkUserPermission:PRODUCT_TYPES_INDEX');
            Route::post('{id}/update', [ProductTypeController::class, 'update'])->middleware('checkUserPermission:PRODUCT_TYPES_EDIT');
            Route::delete('{id}/delete', [ProductTypeController::class, 'destroy'])->middleware('checkUserPermission:PRODUCT_TYPES_DELETE');
            Route::post('{id}/change-status', [ProductTypeController::class, 'changeStatus'])->middleware('checkUserPermission:PRODUCT_TYPES_EDIT');
            Route::get('/active/list', [ProductTypeController::class, 'getActiveProductTypes'])->middleware('checkUserPermission:PRODUCT_TYPES_INDEX');
        });
        Route::group(['prefix' => 'categories'], function () {
            Route::post('/', [CategoryController::class, 'index'])->middleware('checkUserPermission:CATEGORIES_INDEX');
            Route::post('/create', [CategoryController::class, 'store'])->middleware('checkUserPermission:CATEGORIES_CREATE');
            Route::get('{id}/details', [CategoryController::class, 'show'])->middleware('checkUserPermission:CATEGORIES_INDEX');
            Route::post('{id}/update', [CategoryController::class, 'update'])->middleware('checkUserPermission:CATEGORIES_EDIT');
            Route::delete('{id}/delete', [CategoryController::class, 'destroy'])->middleware('checkUserPermission:CATEGORIES_DELETE');
            Route::post('{id}/change-status', [CategoryController::class, 'changeStatus'])->middleware('checkUserPermission:CATEGORIES_EDIT');
            Route::get('/active/list', [CategoryController::class, 'getActiveCategories'])->middleware('checkUserPermission:CATEGORIES_INDEX');
        });
    });

    Route::group(['prefix' => ''], function () {
        Route::post('/sync', [CommonController::class, 'sync']);
        Route::get('/language/{local_key}', [CommonController::class, 'languageTranslationData']);
        Route::get('/privileges-list', [CommonController::class, 'privilegesList']);
        Route::get('/roles-list', [CommonController::class, 'rolesList']);
    });
});
