<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Attendance;
use App\Models\Community;
use App\Models\Location;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    // ระดับความสามารถ เก็บเป็นเลข 1-3 ในตาราง activities
    private const LEVELS = [
        1 => 'เริ่มต้น',
        2 => 'ปานกลาง',
        3 => 'สูง',
    ];

    // index หน้าแรก รวมกิจกรรม (มีตัวกรอง กีฬา / วันที่ / ระดับ)
    public function index(Request $request): View
    {
        $query = Activity::with(['sport', 'location', 'community'])
            ->withCount([
                'participants as registered_count' => fn ($q) => $q->where('status', 'registered'),
            ])
            ->whereDate('date', '>=', today());

        if ($request->filled('sport')) {
            $query->where('sport_id', $request->input('sport'));
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        if ($request->filled('level')) {
            $query->where('skill_level', $request->input('level'));
        }

        $activities = $query->orderBy('date')->orderBy('start_time')->get();
        $sports = Sport::orderBy('name')->get();
        $levels = self::LEVELS;

        return view('activities.index', compact('activities', 'sports', 'levels'));
    }

    // create หน้าเพิ่มกิจกรรม
    public function create(): View
    {
        $sports = Sport::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $communities = Community::where('created_by', auth()->id())->orderBy('name')->get();
        $levels = self::LEVELS;

        return view('activities.create', compact('sports', 'locations', 'communities', 'levels'));
    }

    // store เก็บข้อมูลกิจกรรม
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sport_id' => ['required', 'exists:sports,id'],
            // เลือกได้เฉพาะ community ที่ตัวเองเป็นคนสร้าง
            'community_id' => ['nullable', Rule::exists('communities', 'id')->where('created_by', auth()->id())],
            'location_id' => ['nullable', 'required_without:new_location_name', 'exists:locations,id'],
            'new_location_name' => ['nullable', 'required_without:location_id', 'string', 'max:255'],
            'new_location_address' => ['nullable', 'string', 'max:255'],
            'skill_level' => ['required', 'integer', 'between:1,3'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'max_participants' => ['required', 'integer', 'min:1'],
        ]);

        $locationId = $validated['location_id'] ?? null;

        // ถ้ากรอกสถานที่ใหม่ ให้สร้างสถานที่แล้วใช้อันนั้น
        if (! empty($validated['new_location_name'])) {
            $locationId = Location::create([
                'name' => $validated['new_location_name'],
                'address' => $validated['new_location_address'] ?? null,
            ])->id;
        }

        $activity = Activity::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sport_id' => $validated['sport_id'],
            'community_id' => $validated['community_id'] ?? null,
            'location_id' => $locationId,
            'skill_level' => $validated['skill_level'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'max_participants' => $validated['max_participants'],
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'สร้างกิจกรรมสำเร็จ');
    }

    // show แสดงเฉพาะกิจกรรมนั้น ๆ
    public function show(Activity $activity): View
    {
        $activity->load(['sport', 'location', 'creator', 'community']);

        $registeredCount = $activity->participants()->where('status', 'registered')->count();
        $myParticipation = $activity->participants()->where('user_id', auth()->id())->first();
        $levels = self::LEVELS;

        return view('activities.show', compact('activity', 'registeredCount', 'myParticipation', 'levels'));
    }

    // register กดเข้าร่วมกิจกรรม
    public function register(Activity $activity): RedirectResponse
    {
        $userId = auth()->id();

        $existing = $activity->participants()->where('user_id', $userId)->first();

        if ($existing?->status === 'registered') {
            return back()->with('success', 'คุณลงทะเบียนกิจกรรมนี้แล้ว');
        }

        if (Carbon::parse($activity->date)->isBefore(today())) {
            return back()->withErrors(['activity' => 'กิจกรรมนี้จบไปแล้ว']);
        }

        $registeredCount = $activity->participants()->where('status', 'registered')->count();

        if ($registeredCount >= $activity->max_participants) {
            return back()->withErrors(['activity' => 'กิจกรรมนี้เต็มแล้ว']);
        }

        // ตาราง unique (activity_id, user_id) ถ้าเคยสมัครแล้วยกเลิก ให้อัปเดตแถวเดิม
        ActivityParticipant::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $userId],
            ['status' => 'registered', 'registered_at' => now()],
        );

        return back()->with('success', 'ลงทะเบียนสำเร็จ');
    }

    // myActivities หน้าจัดการกิจกรรมที่ตัวเองสร้าง
    public function myActivities(): View
    {
        $activities = Activity::where('created_by', auth()->id())
            ->with([
                'sport',
                'location',
                'participants' => fn ($q) => $q->where('status', 'registered')->with(['user', 'attendance']),
            ])
            ->orderByDesc('date')
            ->get();

        return view('activities.manage', compact('activities'));
    }

    // checkin เช็คชื่อผู้เข้าร่วม (เฉพาะเจ้าของกิจกรรม)
    public function checkin(Request $request, Activity $activity): RedirectResponse
    {
        abort_unless((int) $activity->created_by === (int) auth()->id(), 403);

        $validated = $request->validate([
            'participant_id' => ['required', 'integer'],
        ]);

        $participant = $activity->participants()
            ->where('status', 'registered')
            ->findOrFail((int) $validated['participant_id']);

        $attendance = Attendance::firstOrCreate(
            ['activity_participant_id' => $participant->id],
            ['checked_in_at' => now(), 'checked_by' => auth()->id()],
        );

        if ($attendance->wasRecentlyCreated) {
            // TODO (คนที่ 5): ให้แต้ม (PointLog) / อัปเดต Streak / เช็ค Badge ตรงนี้
            // ทำเฉพาะตอนเช็คชื่อครั้งแรก จะได้ไม่ให้แต้มซ้ำ
        }

        return back()->with('success', 'เช็คชื่อสำเร็จ');
    }
}
