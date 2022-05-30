<?php

use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\UserController;
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

Route::controller(InvestmentController::class)->group(function () {
    Route::get('/investment/{id}', 'show');
    Route::get('/investments/{user_id}', 'list');
    Route::post('/investment/create', 'create');
    Route::put('/investment/{id}/withdrawal', 'withdrawal');
});

Route::controller(UserController::class)->group(function () {
    Route::delete('/user/{id}', 'destroy');
    Route::get('/user/{id}', 'show');
    Route::get('/users', 'index');
    Route::post('/user', 'store');
    Route::put('/user/{id}', 'update');
});
