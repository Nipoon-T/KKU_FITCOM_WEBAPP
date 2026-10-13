<?php

namespace App\Http\Controllers;

use App\Models\ActivityParticipant;
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
        $profile = $user->profile;

        $totalPoints = $user->totalPoints();
        $streak = $user->streak;

        // ถ้าไม่ได้เข้าร่วมตั้งแต่เมื่อวาน ถือว่า streak ขาดแล้ว แสดงเป็น 0
        $currentStreak = 0;
        if ($streak?->last_activity_date !== null
            && $streak->last_activity_date->greaterThanOrEqualTo(now()->subDay()->startOfDay())) {
            $currentStreak = $streak->current_streak;
        }
        $longestStreak = $streak->longest_streak ?? 0;

        // เหรียญทั้งหมด + id ที่ได้แล้ว
        $badges = Badge::orderBy('condition_type')->orderBy('condition_value')->get();
        $earnedIds = $user->badges()->pluck('badges.id')->all();

        $recentLogs = $user->pointLogs()
            ->with('activity')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // กราฟ: แต้มรายวันย้อนหลัง 14 วัน
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

        // กิจกรรมที่สมัครไว้ (ข้อมูลจากโมดูล Activity ของคนที่ 3)
        $today = now()->toDateString();
        $myActivities = fn () => ActivityParticipant::query()
            ->select('activity_participants.*')
            ->join('activities', 'activities.id', '=', 'activity_participants.activity_id')
            ->where('activity_participants.user_id', $user->id)
            ->where('activity_participants.status', 'registered')
            ->with(['activity.location', 'activity.sport', 'attendance']);

        $upcoming = $myActivities()
            ->where('activities.date', '>=', $today)
            ->orderBy('activities.date')
            ->orderBy('activities.start_time')
            ->limit(5)
            ->get();

        $history = $myActivities()
            ->where('activities.date', '<', $today)
            ->orderByDesc('activities.date')
            ->limit(10)
            ->get();

        return view('dashboard.member', compact(
            'user', 'profile', 'totalPoints', 'currentStreak', 'longestStreak',
            'badges', 'earnedIds', 'recentLogs', 'chartLabels', 'chartData',
            'upcoming', 'history', 'user', 'profile'
        ));
    }
}
