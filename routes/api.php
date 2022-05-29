<?php

use App\Http\Controllers\InvestmentController;
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

Route::get('/investment/{id}', [InvestmentController::class, 'show']);
Route::get('/investments/{user_id}', [InvestmentController::class, 'list']);
Route::post('/investment/create', [InvestmentController::class, 'create']);
Route::put('/investment/{id}/withdrawal', [InvestmentController::class, 'withdrawal']);
