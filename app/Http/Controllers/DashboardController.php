<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\PointLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function member(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $totalPoints = $user->totalPoints();
        $streak = $user->streak;

        // ถ้าไม่ได้เข้าร่วมตั้งแต่เมื่อวาน ถือว่า streak ขาดแล้ว แสดงเป็น 0
        $currentStreak = 0;
        if ($streak?->last_activity_date !== null
            && $streak->last_activity_date->greaterThanOrEqualTo(now()->subDay()->startOfDay())) {
            $currentStreak = $streak->current_streak;
        }
        $longestStreak = $streak->longest_streak ?? 0;

        // เหรียญทั้งหมด + id ที่ได้แล้ว (ไว้แสดงแบบล็อก/ปลดล็อก)
        $badges = Badge::orderBy('condition_type')->orderBy('condition_value')->get();
        $earnedIds = $user->badges()->pluck('badges.id')->all();

        $recentLogs = $user->pointLogs()
            ->with('activity')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // กราฟ: แต้มรายวันย้อนหลัง 14 วัน (วันที่ไม่มีแต้ม = 0)
        $start = now()->subDays(13)->startOfDay();
        $daily = $user->pointLogs()
            ->where('created_at', '>=', $start)
            ->get()
            ->groupBy(fn (PointLog $log): string => $log->created_at?->format('Y-m-d') ?? '')
            ->map(fn ($logs) => $logs->sum('points_change'));

        $chartLabels = [];
        $chartData = [];
        for ($i = 0; $i < 14; $i++) {
            $day = $start->addDays($i);
            $chartLabels[] = $day->format('d/m');
            $chartData[] = (int) ($daily[$day->format('Y-m-d')] ?? 0);
        }

        return view('dashboard.member', compact(
            'totalPoints', 'currentStreak', 'longestStreak', 'badges', 'earnedIds',
            'recentLogs', 'chartLabels', 'chartData'
        ));
    }
}
