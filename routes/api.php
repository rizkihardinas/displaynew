<?php

use App\Http\Controllers\Api\IndexController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('hit-display',[IndexController::class,'hit_display']);
Route::get('/generate-image',[IndexController::class,'generateImage'])->name('image');
Route::get('/video-to-base64', [IndexController::class,'convertToBase64'])->name('video.to.base64');
Route::post('/setting/display',[IndexController::class,'setupSetting']);
Route::post('/setting/access',[IndexController::class,'setupSecurity']);
Route::post('/setting/preference',[IndexController::class,'setupPreference']);