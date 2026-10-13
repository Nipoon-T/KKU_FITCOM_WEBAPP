<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\PointLog;
use App\Models\Sport;
use Illuminate\Http\Request; // ใช้ลบไฟล์รูปปกเก่า
use Illuminate\Support\Facades\Storage; // ใช้สุ่มชื่อไฟล์รูป
use Illuminate\Support\Str; // อ่านคะแนน (ตารางของคนที่ 5)

class CommunityController extends Controller
{
    // หน้ารวม Community ทั้งหมด + ค้นหา/กรอง
    public function index(Request $request)
    {
        // เริ่มสร้างคำสั่ง query (ยังไม่ดึงข้อมูล)
        $query = Community::with('sport')
            ->withCount('approvedMembers'); // ได้ approved_members_count ไว้แสดงจำนวนสมาชิก

        // ถ้ากรอกคำค้น → หาชื่อกลุ่มที่มีคำนั้นอยู่
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%');
        }

        // ถ้าเลือกกีฬา → เอาเฉพาะกลุ่มกีฬานั้น
        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // ถ้าเลือก public/private → กรองตามนั้น
        if ($request->filled('privacy')) {
            $query->where('privacy', $request->privacy);
        }

        $communities = $query->latest()->get(); // ดึงข้อมูลจริง ใหม่สุดก่อน

        $sports = Sport::orderBy('name')->get(); // สำหรับ dropdown กรองกีฬา

        return view('community.index', compact('communities', 'sports'));
    }

    // หน้าฟอร์มสร้าง Community
    public function create()
    {
        $sports = Sport::orderBy('name')->get(); // ข้อมูลสำหรับ dropdown กีฬา

        return view('community.create', compact('sports'));
    }

    // หน้า "จัดการกลุ่มของฉัน"
    public function mine()
    {
        $userId = auth()->id(); // id ของคนที่ล็อกอินอยู่

        // 1) หา id ของกลุ่มที่ฉันเป็นเจ้าของ
        $ownedIds = CommunityMember::where('user_id', $userId)
            ->where('role', 'owner')
            ->pluck('community_id'); // ได้รายการ id เช่น [3, 5]

        // ดึงกลุ่มเหล่านั้น พร้อมนับสมาชิกและคำขอที่รออนุมัติ
        $ownedCommunities = Community::whereIn('id', $ownedIds)
            ->withCount(['approvedMembers', 'pendingMembers'])
            ->latest()
            ->get();

        // 2) กลุ่มที่ฉันเข้าร่วม (เป็น member ทั้ง approved และ pending)
        $memberships = CommunityMember::with(['community' => fn ($q) => $q->withCount('approvedMembers')]) // ดึงข้อมูลกลุ่ม + นับสมาชิก
            ->where('user_id', $userId)
            ->where('role', 'member')
            ->get();

        return view('community.mine', compact('ownedCommunities', 'memberships'));
    }

    // หน้า "จัดการกลุ่มของคุณ" (dashboard ของกลุ่ม — owner เท่านั้น)
    public function manage(Community $community)
    {
        abort_unless($community->isOwner(auth()->user()), 403);

        // 1) สมาชิกที่อนุมัติแล้ว (พร้อมรูปโปรไฟล์) + จำนวนคำขอที่รออยู่
        $members = $community->approvedMembers()->with('user.profile')->get();
        $pendingCount = $community->pendingMembers()->count();

        // 2) สถิติกิจกรรมตั้งแต่เปิดกลุ่ม
        $totalActivities = $community->activities()->count();
        $pastActivities = $community->activities()
            ->whereDate('date', '<', now()->toDateString())
            ->count();

        // 3) อันดับคะแนน: รวมเฉพาะคะแนนที่ได้จาก "กิจกรรมของกลุ่มนี้"
        $activityIds = $community->activities()->pluck('id');

        // ได้ผลแบบ [user_id => คะแนนรวม] เช่น [2 => 30, 5 => 10]
        $pointsByUser = PointLog::whereIn('activity_id', $activityIds)
            ->whereIn('user_id', $members->pluck('user_id'))
            ->groupBy('user_id')
            ->selectRaw('user_id, SUM(points_change) as total')
            ->pluck('total', 'user_id');

        // จับคู่สมาชิกกับคะแนน (ไม่มีคะแนน = 0) แล้วเรียงมากไปน้อย เอา 10 อันดับ
        $leaderboard = $members
            ->map(fn ($member) => [
                'user' => $member->user,
                'points' => (int) ($pointsByUser[$member->user_id] ?? 0),
            ])
            ->sortByDesc('points')
            ->take(10)
            ->values();

        // 4) กิจกรรมที่กำลังจะมาถึง เรียงจากใกล้ที่สุด
        $upcoming = $community->activities()
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('community.manage', compact(
            'community',
            'members',
            'pendingCount',
            'totalActivities',
            'pastActivities',
            'leaderboard',
            'upcoming',
        ));
    }

    // บันทึก Community ใหม่
    public function store(Request $request)
    {
        // ตรวจข้อมูลจากฟอร์ม ถ้าไม่ผ่าน Laravel จะเด้งกลับหน้าเดิมพร้อม error อัตโนมัติ
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sport_id' => ['nullable', 'exists:sports,id'],
            'cover_image_data' => ['nullable', 'string'], // รูปที่บีบแล้ว (ข้อความ Base64 จาก JS)
            'cover_position' => ['nullable', 'string', 'regex:/^\d{1,3}% \d{1,3}%$/'], // ต้องเป็นรูปแบบ "50% 30%"
            'privacy' => ['required', 'in:public,private'],
        ]);

        $validated['created_by'] = auth()->id(); // คนที่ล็อกอินอยู่คือผู้สร้าง

        unset($validated['cover_image_data']); // ไม่ใช่คอลัมน์ในตาราง เอาออกก่อนบันทึก
        // ถ้าไม่ได้ส่งตำแหน่งมา ใช้กึ่งกลาง (คอลัมน์ห้ามเป็น null)
        $validated['cover_position'] = $validated['cover_position'] ?? '50% 50%';

        // ถ้ามีรูปปก → ตรวจและบันทึกด้วยฟังก์ชัน saveCoverImage (อยู่ท้ายไฟล์)
        if ($request->filled('cover_image_data')) {
            $path = $this->saveCoverImage($request->cover_image_data);

            // รูปไม่ผ่านการตรวจ → กลับไปหน้าฟอร์มพร้อมข้อความ error
            if ($path === null) {
                return back()
                    ->withErrors(['cover_image' => 'รูปปกไม่ถูกต้อง กรุณาเลือกรูปใหม่'])
                    ->withInput();
            }

            $validated['cover_image'] = $path; // เก็บ path ไว้บันทึกลง DB
        }

        $community = Community::create($validated);

        // ใหม่: ผู้สร้างกลายเป็น owner ของกลุ่มทันที
        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => auth()->id(),
            'role' => 'owner',
            'status' => 'approved',
        ]);

        return redirect()
            ->route('community.show', $community)
            ->with('success', 'สร้าง Community สำเร็จ');
    }

    // หน้ารายละเอียด Community (Guest ดูได้)
    public function show(Community $community)
    {
        $user = auth()->user(); // ถ้ายังไม่ login จะเป็น null

        $community->load([
            'sport',
            'creator.profile',
            'approvedMembers.user.profile',
            'activities' => fn ($query) => $query
                ->with(['sport', 'location'])
                ->withCount(['participants as registered_count' => fn ($q) => $q->where('status', 'registered')])
                ->whereDate('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->orderBy('start_time'),
        ]);

        // Guest (ไม่ได้ login) จะไม่มีสถานะสมาชิกใดๆ
        $membership = $user ? $community->membershipOf($user) : null;
        $isOwner = $user ? $community->isOwner($user) : false;
        $isApprovedMember = $user ? $community->isApprovedMember($user) : false;

        // โพสต์: ให้เห็นเฉพาะสมาชิกที่อนุมัติแล้ว
        $posts = $isApprovedMember
            ? $community->posts()->with('user')->latest('created_at')->get()
            : collect();

        // คำขอที่รออนุมัติ: ให้เห็นเฉพาะ owner
        $pendingMembers = $isOwner
            ? $community->pendingMembers()->with('user')->get()
            : collect();

        return view('community.show', compact(
            'community',
            'membership',
            'isOwner',
            'isApprovedMember',
            'posts',
            'pendingMembers',
        ));
    }

    // ขอเข้าร่วมกลุ่ม
    public function join(Community $community)
    {
        // กันสมัครซ้ำ: ถ้ามีแถวอยู่แล้วไม่ต้องสร้างใหม่
        $existing = $community->membershipOf(auth()->user());

        if ($existing) {
            $message = $existing->status === 'approved'
                ? 'คุณเป็นสมาชิกกลุ่มนี้อยู่แล้ว'
                : 'คำขอของคุณกำลังรอการอนุมัติ';

            return back()->with('error', $message);
        }

        // public = เข้าได้ทันที, private = ต้องรอ owner อนุมัติ
        $isPublic = $community->privacy === 'public';

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => auth()->id(),
            'role' => 'member',
            'status' => $isPublic ? 'approved' : 'pending',
        ]);

        return back()->with('success', $isPublic
            ? 'เข้าร่วมกลุ่มสำเร็จ'
            : 'ส่งคำขอแล้ว รอเจ้าของกลุ่มอนุมัติ');
    }

    // ออกจากกลุ่ม (หรือยกเลิกคำขอที่ยัง pending)
    public function leave(Community $community)
    {
        $membership = $community->membershipOf(auth()->user());

        if (! $membership) {
            return back()->with('error', 'คุณไม่ได้เป็นสมาชิกกลุ่มนี้');
        }

        // owner ออกไม่ได้ ไม่อย่างนั้นกลุ่มจะไม่มีคนดูแล
        if ($membership->role === 'owner') {
            return back()->with('error', 'เจ้าของกลุ่มไม่สามารถออกจากกลุ่มได้');
        }

        $membership->delete();

        return back()->with('success', 'ออกจากกลุ่มแล้ว');
    }

    // owner อนุมัติคำขอ
    public function approve(Community $community, CommunityMember $member)
    {
        $this->checkOwnerAndMember($community, $member); // เช็คสิทธิ์ก่อนทำ

        $member->update(['status' => 'approved']);

        $message = 'อนุมัติ '.$member->user->name.' แล้ว';

        // ถ้าเรียกมาจาก JavaScript (AJAX) → ตอบเป็น JSON
        if (request()->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        // ถ้าส่งจากฟอร์มปกติ → กลับหน้าเดิม (แบบเดิม)
        return back()->with('success', $message);
    }

    // owner ปฏิเสธคำขอ (ลบแถวทิ้ง ผู้ใช้จะสมัครใหม่ได้)
    public function reject(Community $community, CommunityMember $member)
    {
        $this->checkOwnerAndMember($community, $member);

        $name = $member->user->name; // เก็บชื่อไว้ก่อนลบ
        $member->delete();

        $message = 'ปฏิเสธคำขอของ '.$name.' แล้ว';

        if (request()->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    // โพสต์ข้อความในกลุ่ม
    public function storePost(Request $request, Community $community)
    {
        // เฉพาะสมาชิกที่อนุมัติแล้ว คนอื่นได้หน้า 403
        abort_unless($community->isApprovedMember(auth()->user()), 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $community->posts()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'โพสต์สำเร็จ');
    }

    // หน้าฟอร์มแก้ไขกลุ่ม (owner เท่านั้น)
    public function edit(Community $community)
    {
        // ไม่ใช่เจ้าของ = ห้ามเข้า
        abort_unless($community->isOwner(auth()->user()), 403);

        $sports = Sport::orderBy('name')->get(); // ใช้ทำ dropdown กีฬา

        return view('community.edit', compact('community', 'sports'));
    }

    // บันทึกการแก้ไข (owner เท่านั้น)
    public function update(Request $request, Community $community)
    {
        abort_unless($community->isOwner(auth()->user()), 403);

        // กฎเดียวกับตอนสร้าง
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sport_id' => ['nullable', 'exists:sports,id'],
            'cover_image_data' => ['nullable', 'string'],
            'cover_position' => ['nullable', 'string', 'regex:/^\d{1,3}% \d{1,3}%$/'], // ต้องเป็นรูปแบบ "50% 30%"
            'privacy' => ['required', 'in:public,private'],
        ]);

        unset($validated['cover_image_data']);

        // ถ้าไม่ได้ส่งตำแหน่งมา ใช้กึ่งกลาง (คอลัมน์ห้ามเป็น null)
        $validated['cover_position'] = $validated['cover_position'] ?? '50% 50%';

        // ถ้าเลือกรูปใหม่ → บันทึกรูปใหม่ก่อน ถ้าสำเร็จค่อยลบรูปเก่า
        if ($request->filled('cover_image_data')) {
            $path = $this->saveCoverImage($request->cover_image_data);

            if ($path === null) {
                return back()
                    ->withErrors(['cover_image' => 'รูปปกไม่ถูกต้อง กรุณาเลือกรูปใหม่'])
                    ->withInput();
            }

            if ($community->cover_image) {
                Storage::disk('public')->delete($community->cover_image); // ลบรูปเก่า
            }

            $validated['cover_image'] = $path;
        }

        $community->update($validated); // บันทึกค่าใหม่ลง DB

        return redirect()
            ->route('community.show', $community)
            ->with('success', 'แก้ไขข้อมูลกลุ่มแล้ว');
    }

    // ลบกลุ่ม (owner เท่านั้น)
    public function destroy(Community $community)
    {
        abort_unless($community->isOwner(auth()->user()), 403);

        // ลบไฟล์รูปปก (ถ้ามี)
        if ($community->cover_image) {
            Storage::disk('public')->delete($community->cover_image);
        }

        // ลบกลุ่ม → สมาชิกและโพสต์ถูกลบตาม (cascade ใน migration)
        // กิจกรรมของคนที่ 3 ไม่ถูกลบ แค่ community_id กลายเป็น null
        $community->delete();

        return redirect()
            ->route('community.index')
            ->with('success', 'ลบกลุ่มแล้ว');
    }

    // ===== ตรวจและบันทึกรูปปกที่ส่งมาเป็นข้อความ Base64 =====
    // คืนค่า path ของไฟล์ ถ้าสำเร็จ / คืนค่า null ถ้ารูปไม่ถูกต้อง
    private function saveCoverImage(string $data): ?string
    {
        $prefix = 'data:image/jpeg;base64,';

        // 1) ต้องขึ้นต้นด้วย data:image/jpeg;base64, (JS ของเราส่งแบบนี้เสมอ)
        if (! str_starts_with($data, $prefix)) {
            return null;
        }

        // 2) ตัดส่วนหัวออก แล้วแปลงข้อความกลับเป็นไฟล์รูป (true = ตรวจแบบเข้มงวด)
        $binary = base64_decode(substr($data, strlen($prefix)), true);
        if ($binary === false) {
            return null;
        }

        // 3) ขนาดต้องไม่เกิน 2MB (หลังบีบแล้วปกติแค่หลักร้อย KB)
        if (strlen($binary) > 2 * 1024 * 1024) {
            return null;
        }

        // 4) ตรวจว่าเป็นรูป JPEG จริง (กันคนส่งไฟล์อื่นปลอมมา)
        //    ใส่ @ ข้างหน้าเพื่อไม่ให้ PHP แสดง warning ถ้าไม่ใช่รูป
        $info = @getimagesizefromstring($binary);
        if ($info === false || $info['mime'] !== 'image/jpeg') {
            return null;
        }

        // 5) ตั้งชื่อไฟล์แบบสุ่ม แล้วบันทึกลง storage/app/public/communities
        $path = 'communities/'.Str::random(40).'.jpg';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    // ===== ฟังก์ชันช่วยเช็คสิทธิ์ (ใช้ภายใน Controller นี้เท่านั้น) =====
    private function checkOwnerAndMember(Community $community, CommunityMember $member): void
    {
        // 1) คนกดต้องเป็น owner ของกลุ่มนี้
        abort_unless($community->isOwner(auth()->user()), 403);

        // 2) สมาชิกที่จะจัดการต้องอยู่ในกลุ่มนี้จริง (กันแก้ id ใน URL ไปยุ่งกลุ่มอื่น)
        abort_unless($member->community_id === $community->id, 404);

        // 3) จัดการได้เฉพาะคำขอที่ยัง pending
        abort_unless($member->status === 'pending', 400);
    }
}
