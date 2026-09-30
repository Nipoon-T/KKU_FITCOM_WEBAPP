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
    // ระดับกิจกรรม เก็บเป็นเลข 1-3 ในตาราง activities
    private const LEVELS = [
        1 => 'ง่าย',
        2 => 'ปานกลาง',
        3 => 'เชี่ยวชาญ',
    ];

    // index หน้าแรก ค้นหากิจกรรม
    // ตัวกรอง: q (คำค้น) / sport / date (วันเดียว) / from-to (ช่วงเวลา) / level / location
    public function index(Request $request): View
    {
        $query = Activity::with(['sport', 'location', 'community'])
            ->withCount([
                'participants as registered_count' => fn ($q) => $q->where('status', 'registered'),
            ])
            ->whereDate('date', '>=', today());

        if ($request->filled('q')) {
            $keyword = '%'.$request->string('q')->toString().'%';

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword)
                    ->orWhereHas('community', fn ($c) => $c->where('name', 'like', $keyword));
            });
        }

        if ($request->filled('sport')) {
            $query->where('sport_id', $request->input('sport'));
        }

        if ($request->filled('level')) {
            $query->where('skill_level', $request->input('level'));
        }

        if ($request->filled('location')) {
            $place = '%'.$request->string('location')->toString().'%';

            $query->whereHas('location', fn ($l) => $l->where('name', 'like', $place)->orWhere('address', 'like', $place));
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        // ช่วงเวลา: กิจกรรมต้องเริ่มไม่ก่อน from และจบไม่เกิน to
        if ($request->filled('from')) {
            $query->where('start_time', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('end_time', '<=', $request->input('to'));
        }

        $activities = $query->orderBy('date')->orderBy('start_time')->get();
        $sports = Sport::orderBy('name')->get();
        $levels = self::LEVELS;

        return view('activities.index', compact('activities', 'sports', 'levels'));
    }

    // create หน้าสร้างกิจกรรม
    public function create(): View
    {
        return view('activities.create', $this->formData());
    }

    // store เก็บข้อมูลกิจกรรม
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $activity = Activity::create([
            ...$this->activityData($validated),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'สร้างกิจกรรมสำเร็จ');
    }

    // show รายละเอียดกิจกรรม
    public function show(Activity $activity): View
    {
        $activity->load(['sport', 'location', 'creator', 'community']);

        $registeredCount = $activity->participants()->where('status', 'registered')->count();
        $myParticipation = $activity->participants()->where('user_id', auth()->id())->first();
        $levels = self::LEVELS;

        return view('activities.show', compact('activity', 'registeredCount', 'myParticipation', 'levels'));
    }

    // edit หน้าแก้ไขกิจกรรม (เฉพาะเจ้าของ และกิจกรรมที่ยังไม่จบ)
    public function edit(Activity $activity): View
    {
        $this->abortUnlessOwner($activity);
        abort_if($this->hasEnded($activity), 403, 'กิจกรรมที่จบแล้วแก้ไขไม่ได้');

        return view('activities.edit', [
            'activity' => $activity,
            ...$this->formData(),
        ]);
    }

    // update บันทึกการแก้ไข
    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $this->abortUnlessOwner($activity);
        abort_if($this->hasEnded($activity), 403, 'กิจกรรมที่จบแล้วแก้ไขไม่ได้');

        $registeredCount = $activity->participants()->where('status', 'registered')->count();

        $rules = $this->rules();
        // ลดจำนวนที่รับให้ต่ำกว่าคนที่ลงทะเบียนแล้วไม่ได้
        $rules['max_participants'] = ['required', 'integer', 'min:'.max(1, $registeredCount)];

        $validated = $request->validate($rules);

        $activity->update($this->activityData($validated));

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'แก้ไขกิจกรรมสำเร็จ');
    }

    // destroy ลบกิจกรรม (เฉพาะเจ้าของ และต้องยังไม่มีการเช็คชื่อ)
    public function destroy(Activity $activity): RedirectResponse
    {
        $this->abortUnlessOwner($activity);

        $hasAttendance = Attendance::whereHas(
            'participant',
            fn ($q) => $q->where('activity_id', $activity->id)
        )->exists();

        if ($hasAttendance) {
            return back()->withErrors(['activity' => 'ลบไม่ได้ เพราะมีการเช็คชื่อแล้ว']);
        }

        $activity->delete();

        return redirect()
            ->route('activities.mine')
            ->with('success', 'ลบกิจกรรมสำเร็จ');
    }

    // register กดลงทะเบียนเข้าร่วมกิจกรรม
    public function register(Request $request, Activity $activity): RedirectResponse|JsonResponse
    {
        $userId = auth()->id();

        $existing = $activity->participants()->where('user_id', $userId)->first();

        if ($existing?->status === 'registered') {
            return $this->registerResult($request, 'คุณลงทะเบียนกิจกรรมนี้แล้ว', true);
        }

        if ($this->hasEnded($activity)) {
            return $this->registerResult($request, 'กิจกรรมนี้จบไปแล้ว', false);
        }

        $registeredCount = $activity->participants()->where('status', 'registered')->count();

        if ($registeredCount >= $activity->max_participants) {
            return $this->registerResult($request, 'กิจกรรมนี้เต็มแล้ว', false);
        }

        // ตาราง unique (activity_id, user_id) ถ้าเคยสมัครแล้วยกเลิก ให้อัปเดตแถวเดิม
        ActivityParticipant::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $userId],
            ['status' => 'registered', 'registered_at' => now()],
        );

        return $this->registerResult($request, 'ลงทะเบียนสำเร็จ', true, $registeredCount + 1);
    }

    // myActivities หน้าจัดการกิจกรรมของคุณ (แก้ไข/ลบ + ภาพรวมการเช็คชื่อ)
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

    // attendance หน้ารายชื่อเช็คชื่อของกิจกรรมหนึ่ง
    public function attendance(Activity $activity): View
    {
        $this->abortUnlessOwner($activity);

        $activity->load(['sport', 'location']);

        $participants = $activity->participants()
            ->where('status', 'registered')
            ->with(['user', 'attendance'])
            ->get();

        $canCheckin = $this->isActivityDay($activity);

        return view('activities.attendance', compact('activity', 'participants', 'canCheckin'));
    }

    // checkin เช็คชื่อผู้เข้าร่วม (เฉพาะเจ้าของกิจกรรม และเฉพาะวันจัดกิจกรรม)
    public function checkin(Request $request, Activity $activity): RedirectResponse|JsonResponse
    {
        $this->abortUnlessOwner($activity);

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

    /**
     * ข้อมูลที่ฟอร์มสร้าง/แก้ไขกิจกรรมต้องใช้
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'sports' => Sport::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'communities' => Community::where('created_by', auth()->id())->orderBy('name')->get(),
            'levels' => self::LEVELS,
        ];
    }

    /**
     * กฎ validation ของฟอร์มกิจกรรม (ใช้ร่วมกันทั้ง store และ update)
     *
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:300'],
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
        ];
    }

    /**
     * แปลงข้อมูลที่ผ่าน validation เป็นฟิลด์ของตาราง activities
     * (ถ้ากรอกสถานที่ใหม่ จะสร้างสถานที่แล้วใช้อันนั้น)
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function activityData(array $validated): array
    {
        $locationId = $validated['location_id'] ?? null;

        if (! empty($validated['new_location_name'])) {
            $locationId = Location::create([
                'name' => $validated['new_location_name'],
                'address' => $validated['new_location_address'] ?? null,
            ])->id;
        }

        return [
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
        ];
    }

    private function abortUnlessOwner(Activity $activity): void
    {
        abort_unless((int) $activity->created_by === (int) auth()->id(), 403);
    }

    private function hasEnded(Activity $activity): bool
    {
        return Carbon::parse($activity->date)->isBefore(today());
    }

    private function isActivityDay(Activity $activity): bool
    {
        return Carbon::parse($activity->date)->toDateString() === now('Asia/Bangkok')->toDateString();
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

    private function checkinDenied(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }

        return back()->withErrors(['checkin' => $message]);
    }
}
