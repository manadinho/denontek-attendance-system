<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PublicController;

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

Route::get('update-is-on-whatsapp/{contact}/{status}', [PublicController::class, 'updateIsOnWhatsapp'])->name('students-update-is-on-whatsapp');
Route::get('update-redis-cache', [PublicController::class, 'updateRedisCache'])->name('update-redis-cache');
