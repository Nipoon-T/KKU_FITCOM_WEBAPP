<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('/profile/setup', [ProfileController::class, 'edit'])->name('profile.setup');
    Route::post('/profile/setup', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/settings.php';
Route::get('/test', function () {
    return view('test');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-activities', [ActivityController::class, 'myActivities'])->name('activities.mine');

    Route::prefix('activities')->name('activities.')->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('index');
        Route::get('/create', [ActivityController::class, 'create'])->name('create');
        Route::post('/', [ActivityController::class, 'store'])->name('store');
        Route::get('/{activity}', [ActivityController::class, 'show'])->name('show');
        Route::post('/{activity}/register', [ActivityController::class, 'register'])->name('register');
        Route::post('/{activity}/checkin', [ActivityController::class, 'checkin'])->name('checkin');
    });
});
