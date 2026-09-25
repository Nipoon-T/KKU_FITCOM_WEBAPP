<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommunityController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('/profile/setup', [ProfileController::class, 'edit'])->name('profile.setup');
    Route::post('/profile/setup', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/test', function () {
    return view('test');
});

Route::middleware('auth')->group(function () {
    Route::get('/communities', [CommunityController::class, 'index'])->name('community.index');
    Route::get('/communities/create', [CommunityController::class, 'create'])->name('community.create');
    Route::post('/communities', [CommunityController::class, 'store'])->name('community.store');
    Route::get('/communities/{community}', [CommunityController::class, 'show'])->name('community.show');
});

require __DIR__.'/settings.php';
