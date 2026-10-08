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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    private const LEVELS = [
        1 => 'ง่าย',
        2 => 'ปานกลาง',
        3 => 'เชี่ยวชาญ',
    ];

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

    public function create(): View
    {
        return view('activities.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(requireCover: true));

        $activity = Activity::create([
            ...$this->activityData($request, $validated),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'สร้างกิจกรรมสำเร็จ');
    }

    public function show(Activity $activity): View
    {
        $activity->load(['sport', 'location', 'creator', 'community']);

        $registeredCount = $activity->participants()->where('status', 'registered')->count();
        $myParticipation = $activity->participants()->where('user_id', auth()->id())->first();
        $levels = self::LEVELS;

        return view('activities.show', compact('activity', 'registeredCount', 'myParticipation', 'levels'));
    }

    public function edit(Activity $activity): View
    {
        $this->abortUnlessOwner($activity);
        abort_if($this->hasEnded($activity), 403, 'กิจกรรมที่จบแล้วแก้ไขไม่ได้');

        return view('activities.edit', [
            'activity' => $activity,
            ...$this->formData(),
        ]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $this->abortUnlessOwner($activity);
        abort_if($this->hasEnded($activity), 403, 'กิจกรรมที่จบแล้วแก้ไขไม่ได้');

        $registeredCount = $activity->participants()->where('status', 'registered')->count();

        $rules = $this->rules();
        $rules['max_participants'] = ['required', 'integer', 'min:'.max(1, $registeredCount)];

        $validated = $request->validate($rules);

        $activity->update($this->activityData($request, $validated, $activity));

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'แก้ไขกิจกรรมสำเร็จ');
    }

    public function destroy(Request $request, Activity $activity): RedirectResponse|JsonResponse
    {
        $this->abortUnlessOwner($activity);

        $hasAttendance = Attendance::whereHas(
            'participant',
            fn ($q) => $q->where('activity_id', $activity->id)
        )->exists();

        if ($hasAttendance) {
            $message = 'ลบไม่ได้ เพราะมีการเช็คชื่อแล้ว';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['activity' => $message]);
        }

        if ($activity->cover_image) {
            Storage::disk('public')->delete($activity->cover_image);
        }

        $activity->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'ลบกิจกรรมสำเร็จ']);
        }

        return redirect()
            ->route('activities.mine')
            ->with('success', 'ลบกิจกรรมสำเร็จ');
    }

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

        ActivityParticipant::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $userId],
            ['status' => 'registered', 'registered_at' => now()],
        );

        return $this->registerResult($request, 'ลงทะเบียนสำเร็จ', true, $registeredCount + 1);
    }

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
        $this->abortUnlessOwner($activity);

        $activity->load(['sport', 'location']);

        $participants = $activity->participants()
            ->where('status', 'registered')
            ->with(['user', 'attendance'])
            ->get();

        $canCheckin = $this->isActivityDay($activity);

        return view('activities.attendance', compact('activity', 'participants', 'canCheckin'));
    }

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
            return $this->checkinDenied($request, 'เช็คชื่อได้เฉพาะวันที่จัดกิจกรรมเท่านั้น');
        }

        $attendance = Attendance::firstOrCreate(
            ['activity_participant_id' => $participant->id],
            ['checked_in_at' => now(), 'checked_by' => auth()->id()],
        );

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
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'sports' => Sport::orderBy('name')->get(),
            'locations' => Location::with('sports')->orderBy('name')->get(),
            'communities' => Community::where('created_by', auth()->id())->orderBy('name')->get(),
            'levels' => self::LEVELS,
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $requireCover = false): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:300'],
            'cover_image' => [$requireCover ? 'required' : 'nullable', 'image', 'max:5120'],
            'cover_position' => ['nullable', 'string', 'regex:/^\d{1,3}% \d{1,3}%$/'],
            'sport_id' => ['required', 'exists:sports,id'],
            'community_id' => ['nullable', Rule::exists('communities', 'id')->where('created_by', auth()->id())],
            'location_id' => ['required', 'exists:locations,id'],
            'skill_level' => ['required', 'integer', 'between:1,3'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'max_participants' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function activityData(Request $request, array $validated, ?Activity $activity = null): array
    {
        $coverImage = $activity?->cover_image;
        $coverPosition = $validated['cover_position'] ?? $activity->cover_position ?? '50% 50%';
        if ($request->hasFile('cover_image')) {
            if ($coverImage) {
                Storage::disk('public')->delete($coverImage);
            }

            $coverImage = $request->file('cover_image')->store('activities', 'public');
            $coverPosition = $validated['cover_position'] ?? '50% 50%';
        }

        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'cover_image' => $coverImage,
            'cover_position' => $coverPosition,
            'sport_id' => $validated['sport_id'],
            'community_id' => $validated['community_id'] ?? null,
            'location_id' => $validated['location_id'],
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
