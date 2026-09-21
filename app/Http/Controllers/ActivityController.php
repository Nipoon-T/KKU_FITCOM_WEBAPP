<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Attendance;
use App\Models\Location;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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

        if (Carbon::parse($activity->date)->isBefore(today())) {
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

    public function create(): View
    {
        return view('activities.create', [
            'sports' => Sport::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sport_id' => ['required', 'exists:sports,id'],
            'location_id' => ['nullable', 'required_without:new_location_name', 'exists:locations,id'],
            'new_location_name' => ['nullable', 'required_without:location_id', 'string', 'max:255'],
            'new_location_address' => ['nullable', 'string', 'max:255'],
            'skill_level' => ['required', 'integer', 'between:1,3'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'max_participants' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $locationId = $data['location_id'] ?? null;

        if (! empty($data['new_location_name'])) {
            $locationId = Location::create([
                'name' => $data['new_location_name'],
                'address' => $data['new_location_address'] ?? null,
            ])->id;
        }

        $activity = Activity::create([
            ...Arr::except($data, ['location_id', 'new_location_name', 'new_location_address']),
            'location_id' => $locationId,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('activities.show', $activity)->with('status', 'สร้างกิจกรรมสำเร็จ');
    }

    public function myActivities(): View
    {
        $activities = Activity::query()
            ->where('created_by', auth()->id())
            ->with([
                'sport',
                'location',
                'participants' => fn ($query) => $query
                    ->where('status', 'registered')
                    ->with(['user', 'attendance']),
            ])
            ->orderByDesc('date')
            ->orderBy('start_time')
            ->get();

        return view('activities.manage', ['activities' => $activities]);
    }

    public function checkin(Request $request, Activity $activity): RedirectResponse
    {
        abort_unless((int) $activity->created_by === (int) auth()->id(), 403);

        $data = $request->validate([
            'participant_id' => ['required', 'integer'],
        ]);

        $participant = $activity->participants()
            ->where('status', 'registered')
            ->findOrFail((int) $data['participant_id']);

        Attendance::firstOrCreate(
            ['activity_participant_id' => $participant->id],
            ['checked_in_at' => now(), 'checked_by' => auth()->id()],
        );

        // TODO (คนที่ 5): เรียกให้แต้มตรงนี้ หลังตกลงกันว่าจะเป็น Service หรือ Event

        return back()->with('status', 'เช็คชื่อสำเร็จ');
    }
}
