<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Attendance;
use App\Models\PointLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // ตารางผู้ใช้ ค้นหาด้วยชื่อหรืออีเมล
        $search = trim((string) $request->query('q', ''));
        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            ))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // ถ้า JavaScript ขอมา ส่งกลับแค่ตาราง ไม่ต้องคำนวณสถิติ/กราฟ
        if ($request->ajax()) {
            return view('admin.partials.users-table', compact('users'));
        }

        // ตัวเลขสรุปภาพรวม
        $stats = [
            'users' => User::count(),
            'activities' => Activity::count(),
            'registrations' => ActivityParticipant::count(),
            'checkins' => Attendance::count(),
            'points' => (int) PointLog::sum('points_change'),
        ];

        // กราฟ 14 วัน: สมาชิกใหม่ และจำนวนการเช็คชื่อ
        $start = now()->subDays(13)->startOfDay();

        $newUsers = User::where('created_at', '>=', $start)->get()
            ->groupBy(fn ($u) => CarbonImmutable::parse($u->created_at)->format('Y-m-d'))
            ->map->count();

        $checkins = Attendance::where('checked_in_at', '>=', $start)->get()
            ->groupBy(fn ($a) => CarbonImmutable::parse($a->checked_in_at)->format('Y-m-d'))
            ->map->count();

        $chartLabels = [];
        $chartUsers = [];
        $chartCheckins = [];
        for ($i = 0; $i < 14; $i++) {
            $key = $start->addDays($i)->format('Y-m-d');
            $chartLabels[] = $start->addDays($i)->format('d/m');
            $chartUsers[] = (int) ($newUsers[$key] ?? 0);
            $chartCheckins[] = (int) ($checkins[$key] ?? 0);
        }

        // อันดับแต้มสูงสุด 5 คน
        $leaderboard = PointLog::select('user_id', DB::raw('SUM(points_change) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('user')
            ->get();

        // ตารางผู้ใช้ ค้นหาด้วยชื่อหรืออีเมล
        $search = trim((string) $request->query('q', ''));
        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            ))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.dashboard', compact(
            'stats', 'chartLabels', 'chartUsers', 'chartCheckins',
            'leaderboard', 'users', 'search'
        ));
    }
}
