<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Attendance;
use App\Models\Community;
use App\Models\Location;
use App\Models\Sport;
use Illuminate\Http\JsonResponse;
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
    public function register(Request $request, Activity $activity): RedirectResponse|JsonResponse
    {
        $userId = auth()->id();

        $existing = $activity->participants()->where('user_id', $userId)->first();

        if ($existing?->status === 'registered') {
            return $this->registerResult($request, 'คุณลงทะเบียนกิจกรรมนี้แล้ว', true);
        }

        if (Carbon::parse($activity->date)->isBefore(today())) {
            return $this->registerResult($request, 'กิจกรรมนี้จบไปแล้ว', false);
        }

        $registeredCount = $activity->participants()->where('status', 'registered')->count();

        if ($registeredCount >= $activity->max_participants) {
            return $this->registerResult($request, 'กิจกรรมนี้เต็มแล้ว', false);
        }

        ActivityParticipant::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $userId],
            ['status' => 'registered', 'registered_at' => now()],
        );

        return $this->registerResult($request, 'ลงทะเบียนสำเร็จ', true, $registeredCount + 1);
    }

    private function registerResult(Request $request, string $message, bool $ok, ?int $count = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message, 'count' => $count], $ok ? 200 : 422);
        }

        return $ok
            ? back()->with('success', $message)
            : back()->withErrors(['activity' => $message]);
    }

    // myActivities หน้ารวมกิจกรรมที่ตัวเองสร้าง
    public function myActivities(): View
    {
        $activities = Activity::where('created_by', auth()->id())
            ->with(['sport', 'location'])
            ->withCount([
                'participants as registered_count' => fn ($q) => $q->where('status', 'registered'),
                'participants as checked_count' => fn ($q) => $q->where('status', 'registered')->whereHas('attendance'),
            ])
            ->orderByDesc('date')
            ->get();

        return view('activities.manage', compact('activities'));
    }

    public function attendance(Activity $activity): View
    {
        abort_unless((int) $activity->created_by === (int) auth()->id(), 403);

        $activity->load(['sport', 'location']);

        $participants = $activity->participants()
            ->where('status', 'registered')
            ->with(['user', 'attendance'])
            ->get();

        $canCheckin = $this->isActivityDay($activity);

        return view('activities.attendance', compact('activity', 'participants', 'canCheckin'));
    }

    // checkin เช็คชื่อผู้เข้าร่วม (เฉพาะเจ้าของกิจกรรม)
    public function checkin(Request $request, Activity $activity): RedirectResponse|JsonResponse
    {
        abort_unless((int) $activity->created_by === (int) auth()->id(), 403);

        $validated = $request->validate([
            'participant_id' => ['required', 'integer'],
        ]);

        $participant = $activity->participants()
            ->where('status', 'registered')
            ->findOrFail((int) $validated['participant_id']);

        if (! $this->isActivityDay($activity)) {
            return $this->checkinDenied($request, 'เช็คชื่อได้เฉพาะวันที่จัดกิจกรรม');
        }

        $attendance = Attendance::firstOrCreate(
            ['activity_participant_id' => $participant->id],
            ['checked_in_at' => now(), 'checked_by' => auth()->id()],
        );

        if ($attendance->wasRecentlyCreated) {
            // TODO (คนที่ 5): ให้แต้ม (PointLog) / อัปเดต Streak / เช็ค Badge ตรงนี้
            // ทำเฉพาะตอนเช็คชื่อครั้งแรก จะได้ไม่ให้แต้มซ้ำ
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'เช็คชื่อสำเร็จ',
                'time' => Carbon::parse($attendance->checked_in_at)->timezone('Asia/Bangkok')->format('H:i'),
            ]);
        }

        return back()->with('success', 'เช็คชื่อสำเร็จ');
    }

    private function isActivityDay(Activity $activity): bool
    {
        return Carbon::parse($activity->date)->toDateString() === now('Asia/Bangkok')->toDateString();
    }

    private function checkinDenied(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }

        return back()->withErrors(['checkin' => $message]);
    }
}
