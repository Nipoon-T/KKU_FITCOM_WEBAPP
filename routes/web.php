<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserBadgeController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MatchingController;
use App\Http\Controllers\ProfileController;
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

// ===== Community (คนที่ 2) — route ที่ต้องล็อกอิน =====
Route::middleware('auth')->group(function () {
    // สร้างกลุ่ม
    Route::get('/communities/create', [CommunityController::class, 'create'])->name('community.create'); // ต้องอยู่ก่อน {community} ไม่งั้น "create" จะถูกมองเป็น id
    Route::get('/communities/my', [CommunityController::class, 'mine'])->name('community.mine'); // จัดการกลุ่มของฉัน (ต้องอยู่ก่อน {community} เหมือน create)
    Route::post('/communities', [CommunityController::class, 'store'])->name('community.store');

    // owner: แก้ไข / ลบกลุ่ม
    Route::get('/communities/{community}/edit', [CommunityController::class, 'edit'])->name('community.edit');
    Route::get('/communities/{community}/manage', [CommunityController::class, 'manage'])->name('community.manage'); // dashboard ของกลุ่ม (owner)
    Route::put('/communities/{community}', [CommunityController::class, 'update'])->name('community.update');
    Route::delete('/communities/{community}', [CommunityController::class, 'destroy'])->name('community.destroy');

    // สมาชิก: เข้าร่วม / ออกจากกลุ่ม
    Route::post('/communities/{community}/join', [CommunityController::class, 'join'])->name('community.join');
    Route::post('/communities/{community}/leave', [CommunityController::class, 'leave'])->name('community.leave');

    // owner: อนุมัติ / ปฏิเสธคำขอ ({member} = id ในตาราง community_members)
    Route::post('/communities/{community}/members/{member}/approve', [CommunityController::class, 'approve'])->name('community.approve');
    Route::post('/communities/{community}/members/{member}/reject', [CommunityController::class, 'reject'])->name('community.reject');

    // โพสต์ในกลุ่ม
    Route::post('/communities/{community}/posts', [CommunityController::class, 'storePost'])->name('community.posts.store');
});

// ===== Activity (คนที่ 3) — route ที่ต้องล็อกอิน =====
Route::middleware('auth')->group(function () {
    Route::get('/my-activities', [ActivityController::class, 'myActivities'])->name('activities.mine');

    Route::get('/activities/create', [ActivityController::class, 'create'])->name('activities.create');
    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::get('/activities/{activity}/edit', [ActivityController::class, 'edit'])->name('activities.edit');
    Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
    Route::post('/activities/{activity}/register', [ActivityController::class, 'register'])->name('activities.register');
    Route::get('/activities/{activity}/attendance', [ActivityController::class, 'attendance'])->name('activities.attendance');
    Route::post('/activities/{activity}/checkin', [ActivityController::class, 'checkin'])->name('activities.checkin');
    Route::get('/recommendations', [MatchingController::class, 'index'])->name('recommendations.index');
});

// ===== Guest ดูได้โดยไม่ต้อง login =====
// ต้องอยู่หลังกลุ่มด้านบน เพื่อให้ /create และ /my ถูกจับคู่ก่อน {community} / {activity}
Route::get('/communities', [CommunityController::class, 'index'])->name('community.index');
Route::get('/communities/{community}', [CommunityController::class, 'show'])->name('community.show');
Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // มอบ/ยึดเหรียญรายคน
    Route::get('users/{user}', [UserBadgeController::class, 'show'])->name('users.show');
    Route::post('users/{user}/badges/{badge}', [UserBadgeController::class, 'store'])->name('users.badges.store');
    Route::delete('users/{user}/badges/{badge}', [UserBadgeController::class, 'destroy'])->name('users.badges.destroy');

    // สร้าง/ลบเหรียญพิเศษ
    Route::get('badges', [BadgeController::class, 'index'])->name('badges.index');
    Route::post('badges', [BadgeController::class, 'store'])->name('badges.store');
    Route::delete('badges/{badge}', [BadgeController::class, 'destroy'])->name('badges.destroy');
});

require __DIR__.'/settings.php';
