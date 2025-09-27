<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\V1\Admin\LoginController;
use App\Http\Controllers\V1\Admin\UserController;
use App\Http\Controllers\V1\CommonController;
use App\Http\Controllers\V1\CmsPageController;
use App\Http\Controllers\V1\Admin\RoleController;
use App\Http\Controllers\V1\Admin\ProductTypeController;
use App\Http\Controllers\V1\Admin\CategoryController;
use App\Http\Controllers\V1\Admin\PriceTypeController;
use App\Http\Controllers\V1\Admin\ProductController;
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
            Route::post('/reorder', [ProductTypeController::class, 'reorder'])->middleware('checkUserPermission:PRODUCT_TYPES_EDIT');
        });
        Route::group(['prefix' => 'categories'], function () {
            Route::post('/', [CategoryController::class, 'index'])->middleware('checkUserPermission:CATEGORIES_INDEX');
            Route::post('/create', [CategoryController::class, 'store'])->middleware('checkUserPermission:CATEGORIES_CREATE');
            Route::get('{id}/details', [CategoryController::class, 'show'])->middleware('checkUserPermission:CATEGORIES_INDEX');
            Route::post('{id}/update', [CategoryController::class, 'update'])->middleware('checkUserPermission:CATEGORIES_EDIT');
            Route::delete('{id}/delete', [CategoryController::class, 'destroy'])->middleware('checkUserPermission:CATEGORIES_DELETE');
            Route::post('{id}/change-status', [CategoryController::class, 'changeStatus'])->middleware('checkUserPermission:CATEGORIES_EDIT');
            Route::get('/active/list', [CategoryController::class, 'getActiveCategories'])->middleware('checkUserPermission:CATEGORIES_INDEX');
            Route::post('/reorder', [CategoryController::class, 'reorder'])->middleware('checkUserPermission:CATEGORIES_EDIT');
        });
        Route::group(['prefix' => 'price-types'], function () {
            Route::post('/', [PriceTypeController::class, 'index'])->middleware('checkUserPermission:PRICING_TYPES_INDEX');
            Route::post('/create', [PriceTypeController::class, 'store'])->middleware('checkUserPermission:PRICING_TYPES_CREATE');
            Route::get('{id}/details', [PriceTypeController::class, 'show'])->middleware('checkUserPermission:PRICING_TYPES_INDEX');
            Route::post('{id}/update', [PriceTypeController::class, 'update'])->middleware('checkUserPermission:PRICING_TYPES_EDIT');
            Route::delete('{id}/delete', [PriceTypeController::class, 'destroy'])->middleware('checkUserPermission:PRICING_TYPES_DELETE');
            Route::post('{id}/change-status', [PriceTypeController::class, 'changeStatus'])->middleware('checkUserPermission:PRICING_TYPES_EDIT');
            Route::get('/active/list', [PriceTypeController::class, 'getActivePriceTypes'])->middleware('checkUserPermission:PRICING_TYPES_INDEX');
            Route::get('/product-type/{productTypeId}', [PriceTypeController::class, 'getPriceTypesByProductType'])->middleware('checkUserPermission:PRICING_TYPES_INDEX');
        });

        Route::group(['prefix' => 'products'], function () {
            Route::post('/', [ProductController::class, 'index'])->middleware('checkUserPermission:PRODUCTS_INDEX');
            Route::post('/create', [ProductController::class, 'store'])->middleware('checkUserPermission:PRODUCTS_CREATE');
            Route::get('{id}/details', [ProductController::class, 'show'])->middleware('checkUserPermission:PRODUCTS_INDEX');
            Route::post('{id}/update', [ProductController::class, 'update'])->middleware('checkUserPermission:PRODUCTS_EDIT');
            Route::delete('{id}/delete', [ProductController::class, 'destroy'])->middleware('checkUserPermission:PRODUCTS_DELETE');
            Route::post('{id}/change-status', [ProductController::class, 'changeStatus'])->middleware('checkUserPermission:PRODUCTS_EDIT');
            Route::get('/active/list', [ProductController::class, 'getActiveProducts'])->middleware('checkUserPermission:PRODUCTS_INDEX');
            Route::post('/reorder', [ProductController::class, 'reorder'])->middleware('checkUserPermission:PRODUCTS_EDIT');
        });

        // Menu generation for admin
        Route::post('admin/generate-menu', [App\Http\Controllers\V1\Admin\MenuController::class, 'generateMenu'])
            ->middleware('checkUserPermission:MENU_GENERATE');
    });

    Route::group(['prefix' => ''], function () {
        Route::post('/sync', [CommonController::class, 'sync']);
        Route::get('/language/{local_key}', [CommonController::class, 'languageTranslationData']);
        Route::get('/privileges-list', [CommonController::class, 'privilegesList']);
        Route::get('/roles-list', [CommonController::class, 'rolesList']);
    });
});

// Menu retrieval for frontend
Route::get('menu', [App\Http\Controllers\V1\CommonController::class, 'getMenu']);
