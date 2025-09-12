<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Swagger Documentation Routes
Route::get('/api/documentation', function () {
    $swaggerJson = file_get_contents(storage_path('api-docs/api-docs.json'));
    $swaggerData = json_decode($swaggerJson, true);

    return view('swagger-ui', compact('swaggerData'));
});

Route::get('/docs/api-docs.json', function () {
    $swaggerJson = file_get_contents(storage_path('api-docs/api-docs.json'));
    return response($swaggerJson, 200, ['Content-Type' => 'application/json']);
});
