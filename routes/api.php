<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(\App\Http\Controllers\Api\UsersController::class)
    ->prefix('users')
    ->group(static function () {
        Route::get('/', 'index');
        Route::get('/{userId}', 'show')->where('userId', '[0-9]+')->middleware(\App\Http\Middleware\CheckUserExists::class);
        Route::get('/search', 'search');
        Route::get('/top3', 'top3');
    });

Route::controller(\App\Http\Controllers\Api\UserRepositoryController::class)
    ->prefix('users/{userId}/repositories')
    ->middleware(\App\Http\Middleware\CheckUserExists::class)
    ->group(static function () {
        Route::get('/', 'index')->where('userId', '[0-9]+');
    });
