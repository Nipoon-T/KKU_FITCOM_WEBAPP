<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'sport' => ['nullable', 'integer'],
            'date' => ['nullable', 'date'],
            'level' => ['nullable', 'integer', 'between:1,3'],
        ]);

        $activities = Activity::query()
            ->with(['sport', 'location'])
            ->withCount([
                'participants as registered_count' => fn ($query) => $query->where('status', 'registered'),
            ])
            ->whereDate('date', '>=', today())
            ->when($filters['sport'] ?? null, fn ($query, $sport) => $query->where('sport_id', $sport))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date))
            ->when($filters['level'] ?? null, fn ($query, $level) => $query->where('skill_level', $level))
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(12)
            ->withQueryString();

        return view('activities.index', [
            'activities' => $activities,
            'sports' => Sport::orderBy('name')->get(),
        ]);
    }

    public function show(Activity $activity): View
    {
        $activity->load(['sport', 'location', 'creator']);

        $registeredCount = $activity->participants()
            ->where('status', 'registered')
            ->count();

        $myParticipation = $activity->participants()
            ->where('user_id', auth()->id())
            ->first();

        return view('activities.show', [
            'activity' => $activity,
            'registeredCount' => $registeredCount,
            'myParticipation' => $myParticipation,
        ]);
    }

    public function register(Activity $activity): RedirectResponse
    {
        $userId = auth()->id();

        $existing = $activity->participants()
            ->where('user_id', $userId)
            ->first();

        if ($existing?->status === 'registered') {
            return back()->with('status', 'คุณลงทะเบียนกิจกรรมนี้แล้ว');
        }

        if ($activity->date->isBefore(today())) {
            return back()->withErrors(['activity' => 'กิจกรรมนี้จบไปแล้ว']);
        }

        $registeredCount = $activity->participants()
            ->where('status', 'registered')
            ->count();

        if ($registeredCount >= $activity->max_participants) {
            return back()->withErrors(['activity' => 'กิจกรรมนี้เต็มแล้ว']);
        }

        // updateOrCreate เพราะมี unique (activity_id, user_id) สมัครใหม่หลังยกเลิกต้องอัปเดตแถวเดิม
        ActivityParticipant::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $userId],
            ['status' => 'registered', 'registered_at' => now()],
        );

        return back()->with('status', 'ลงทะเบียนสำเร็จ');
    }
}