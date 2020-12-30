<?php

use App\Http\Controllers\ApiLoginController;
use App\Http\Controllers\EventCommentController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [ApiLoginController::class, 'token']);
Route::middleware('auth:sanctum')->get('/user', [ApiLoginController::class, 'user']);

Route::group(['middleware' => 'auth:sanctum', 'prefix' => '/events'], function () {
    Route::get('/', [EventController::class, 'index']);
    Route::post('/', [EventController::class, 'store']);
    Route::get('/{id}', [EventController::class, 'show']);
    Route::put('/{id}', [EventController::class, 'update']);
    Route::delete('/{id}', [EventController::class, 'destroy']);
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => '/comments'], function () {
    Route::post('/', [EventCommentController::class, 'store']);
    Route::delete('/{id}', [EventCommentController::class, 'destroy']);
});

