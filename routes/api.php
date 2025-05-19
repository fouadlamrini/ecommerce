<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
Route::get('/test', [App\Http\Controllers\test::class, 'test']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

Route::prefix('user')->middleware('api_auth')->group(function(){
    Route::get('/getUser', [App\Http\Controllers\UserController::class, 'getUser']);
});
