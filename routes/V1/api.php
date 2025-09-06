<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\V1\Admin\LoginController;
use App\Http\Controllers\V1\Admin\RolesController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\CommonController;
use App\Http\Controllers\V1\ContactController;
use App\Http\Controllers\V1\CmsPageController;
use App\Http\Controllers\V1\EmpRangeController;
use App\Http\Controllers\V1\MyPurchaseListController;
use App\Http\Controllers\V1\SalesVolumeController;
use App\Http\Controllers\V1\SettingController;
use App\Http\Controllers\V1\UploadCsvContactController;
use Illuminate\Support\Facades\Mail;
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



/** @var \Dingo\Api\Routing\ */
Route::group(['middleware' => ['api']], function () {
    Route::group(['prefix' => 'admin'], function () {
        
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
            Route::get('/profile-details', [LoginController::class, 'profileDetails'])->middleware('checkUserPermission:PROFILE_INDEX');
            Route::get('/logout', [LoginController::class, 'logout']);
            
            Route::get('/view-log', [CommonController::class, 'viewLog']);
            Route::get('/api-logs', [CommonController::class, 'viewApiLogs']);
            
            // Disposition Manager routes
            Route::group(['prefix' => 'disposition-managers'], function () {
                Route::post('/', [UserController::class, 'index'])->middleware('checkUserPermission:DISPOSITION_MANAGER_MANAGEMENT_INDEX');
                Route::post('/create', [UserController::class, 'store'])->middleware('checkUserPermission:DISPOSITION_MANAGER_MANAGEMENT_CREATE');
                Route::get('{id}/details', [UserController::class, 'show'])->middleware('checkUserPermission:DISPOSITION_MANAGER_MANAGEMENT_DETAILS');
                Route::post('{id}/update', [UserController::class, 'update'])->middleware('checkUserPermission:DISPOSITION_MANAGER_MANAGEMENT_UPDATE');
                Route::delete('{id}/delete', [UserController::class, 'destroy'])->middleware('checkUserPermission:DISPOSITION_MANAGER_MANAGEMENT_DELETE');
                Route::post('{id}/change-status', [UserController::class, 'changeStatus'])->middleware('checkUserPermission:DISPOSITION_MANAGER_MANAGEMENT_CHANGE_STATUS');
            });
        
        });
    });

    Route::group(['prefix' => ''], function () {
        Route::post('/sync', [CommonController::class, 'sync']);
        Route::get('/language/{local_key}', [CommonController::class, 'languageTranslationData']);
       
        Route::get('/user-list', [CommonController::class, 'users']);
        Route::get('/privileges-list', [CommonController::class, 'privilegesList']);
        
        
        
      
    });
});
