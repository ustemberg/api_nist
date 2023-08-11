<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\FixedController;
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

Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
});

Route::controller(FixedController::class)->group(function(){
    Route::get('/all', 'all');
    Route::get('/all_by_severity', 'all_by_severity');
    Route::get('/fixed', 'fixed');
    Route::get('/all_by_severity_notfixed', 'all_by_severity_notfixed');
    Route::post('/fix', 'fix');
});
