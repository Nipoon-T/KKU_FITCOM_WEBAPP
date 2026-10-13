<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserBadgeController extends Controller
{
    public function show(User $user): View
    {
        $badges = Badge::orderBy('condition_type')->orderBy('condition_value')->get();

        // [badge_id => earned_at] ของเหรียญที่คนนี้มีแล้ว
        $earned = UserBadge::where('user_id', $user->id)->pluck('earned_at', 'badge_id');

        return view('admin.users.show', compact('user', 'badges', 'earned'));
    }

    // มอบเหรียญให้ผู้ใช้
    public function store(User $user, Badge $badge): JsonResponse
    {
        $exists = UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->exists();

        if (! $exists) {
            UserBadge::create([
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'earned_at' => now(),
            ]);
        }

        return response()->json(['earned' => true, 'earned_at' => now()->format('d/m/Y')]);
    }

    // ยึดเหรียญคืน
    public function destroy(User $user, Badge $badge): JsonResponse
    {
        UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->delete();

        return response()->json(['earned' => false]);
    }
}
