<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StandardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\GoogleLoginController;
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

Route::get('/', function () {return view('home');});

Route::group(['middleware' => 'auth'], function () {

    // Owner Specific Routes Start
    Route::group(['middleware' => 'check.usertype:owner'], function() {
        Route::get('/schools', [HomeController::class, 'schools'])->name('schools');
        Route::get('/select-school/{school_id}', [HomeController::class, 'selectSchool'])->name('select-school');
    });
    // Owner Specific Routes End

    // Common Routes
    Route::group(['middleware' => 'check.schoolid.session'], function () {
        Route::get('/dashboard',[HomeController::class, 'index'])->name('dashboard'); 

        Route::group(['prefix' => 'staff', 'as' => 'staff.'], function() {
            Route::get('/index', [StaffController::class, 'index'])->name('index');
            Route::post('/store', [StaffController::class, 'store'])->name('store');
            Route::get('/delete/{teacher_id}', [StaffController::class, 'destroy'])->name('destroy');
        });

        Route::group(['prefix' => 'standards', 'as' => 'standards.'], function () {
            Route::get('/index', [StandardController::class, 'index'])->name('index');
            Route::post('/store', [StandardController::class, 'store'])->name('store');
            Route::get('/delete/{standard_id}', [StandardController::class, 'destroy'])->name('destroy');
        });

        Route::group(['prefix' => 'students', 'as' => 'students.'], function () {
            Route::get('/index', [StudentController::class, 'index'])->name('index');
            Route::post('/store', [StudentController::class, 'store'])->name('store');
            Route::get('/delete/{student_id}', [StudentController::class, 'destroy'])->name('destroy');
        });

        Route::get('/school-settings/edit', [SettingController::class, 'editSchoolSettings'])->name('school-settings.edit');
        Route::post('/school-settings/update', [SettingController::class, 'updateSchoolSettings'])->name('school-settings.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/google/redirect', [GoogleLoginController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');

// Device Routes
Route::get('/device/register-rfid', [DeviceController::class, 'registerRfid'])->name('device.register-rfid');
Route::get('/device/mark-attendance', [DeviceController::class, 'markAttendance'])->name('device.mark-attendance');

require __DIR__.'/auth.php';
