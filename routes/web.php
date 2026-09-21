<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityController;
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

Route::middleware(['auth', 'verified'])
    ->prefix('activities')
    ->name('activities.')
    ->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('index');
        Route::get('/{activity}', [ActivityController::class, 'show'])->name('show');
        Route::post('/{activity}/register', [ActivityController::class, 'register'])->name('register');
    });