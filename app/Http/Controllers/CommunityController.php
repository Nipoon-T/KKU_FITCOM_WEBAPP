<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Sport;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    // หน้ารวม Community ทั้งหมด
    public function index()
    {
        $communities = Community::with('sport')
            ->withCount('approvedMembers') // ได้ตัวแปร approved_members_count ไว้แสดงจำนวนสมาชิก
            ->latest()
            ->get();

        return view('community.index', compact('communities'));
    }

    // หน้าฟอร์มสร้าง Community
    public function create()
    {
        $sports = Sport::orderBy('name')->get(); // ข้อมูลสำหรับ dropdown กีฬา

        return view('community.create', compact('sports'));
    }

    // บันทึก Community ใหม่
    public function store(Request $request)
    {
        // ตรวจข้อมูลจากฟอร์ม ถ้าไม่ผ่าน Laravel จะเด้งกลับหน้าเดิมพร้อม error อัตโนมัติ
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sport_id' => ['nullable', 'exists:sports,id'],
            'cover_image' => ['nullable', 'image', 'max:5120'], // รูปไม่เกิน 5MB
            'privacy' => ['required', 'in:public,private'],
        ]);

        $validated['created_by'] = auth()->id(); // คนที่ล็อกอินอยู่คือผู้สร้าง

        // ถ้ามีรูปปก ให้เก็บไว้ที่ storage/app/public/communities
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')
                ->store('communities', 'public');
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

    // หน้ารายละเอียด Community
    public function show(Community $community)
    {
        $user = auth()->user();

        $community->load([
            'sport',
            'creator',
            'approvedMembers.user', // รายชื่อสมาชิกที่อนุมัติแล้ว
            // กิจกรรมที่ยังไม่ถึงวัน เรียงจากใกล้ไปไกล (ตารางของคนที่ 3)
            'activities' => fn ($query) => $query
                ->whereDate('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->orderBy('start_time'),
        ]);

        $membership = $community->membershipOf($user);      // แถวสมาชิกของเรา (หรือ null)
        $isOwner = $community->isOwner($user);              // เป็นเจ้าของไหม
        $isApprovedMember = $community->isApprovedMember($user); // เป็นสมาชิกแล้วไหม

        // โพสต์: ให้เห็นเฉพาะสมาชิกที่อนุมัติแล้ว
        $posts = $isApprovedMember
            ? $community->posts()->with('user')->latest('created_at')->get()
            : collect(); // คนนอกได้รายการว่าง

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

        return back()->with('success', 'อนุมัติ ' . $member->user->name . ' แล้ว');
    }

    // owner ปฏิเสธคำขอ (ลบแถวทิ้ง ผู้ใช้จะสมัครใหม่ได้)
    public function reject(Community $community, CommunityMember $member)
    {
        $this->checkOwnerAndMember($community, $member);

        $name = $member->user->name; // เก็บชื่อไว้ก่อนลบ
        $member->delete();

        return back()->with('success', 'ปฏิเสธคำขอของ ' . $name . ' แล้ว');
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