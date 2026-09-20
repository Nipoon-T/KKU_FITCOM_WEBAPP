<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

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