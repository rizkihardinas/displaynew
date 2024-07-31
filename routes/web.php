<?php

use App\Http\Controllers\Api\IndexController as ApiIndexController;
use App\Http\Controllers\Frontend\IndexController;
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

Route::get('/',[IndexController::class,'in'])->name('in');
Route::get('/out',[IndexController::class,'out'])->name('out');

