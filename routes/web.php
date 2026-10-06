<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MatchingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'member'])->name('dashboard');

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

Route::middleware('auth')->group(function () {
    Route::get('/my-activities', [ActivityController::class, 'myActivities'])->name('activities.mine');

    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/create', [ActivityController::class, 'create'])->name('activities.create');
    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
    Route::get('/activities/{activity}/edit', [ActivityController::class, 'edit'])->name('activities.edit');
    Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
    Route::post('/activities/{activity}/register', [ActivityController::class, 'register'])->name('activities.register');
    Route::get('/activities/{activity}/attendance', [ActivityController::class, 'attendance'])->name('activities.attendance');
    Route::post('/activities/{activity}/checkin', [ActivityController::class, 'checkin'])->name('activities.checkin');
    Route::get('/recommendations', [MatchingController::class, 'index'])->name('recommendations');});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';
