<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // ใช้ลบไฟล์รูปปกเก่า
use Illuminate\Support\Str; // ใช้สุ่มชื่อไฟล์รูป

class CommunityController extends Controller
{
    public function index()
    {
        $communities = Community::with('sport')
            ->latest()
            ->get();

        return view('community.index', compact('communities', 'sports'));
    }

    // หน้าฟอร์มสร้าง Community
    public function create()
    {
        $sports = Sport::orderBy('name')->get(); // ข้อมูลสำหรับ dropdown กีฬา

        return view('community.create', compact('sports'));
    }

    public function store(Request $request)
    {
        // ตรวจข้อมูลจากฟอร์ม ถ้าไม่ผ่าน Laravel จะเด้งกลับหน้าเดิมพร้อม error อัตโนมัติ
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sport_id' => ['nullable', 'exists:sports,id'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'privacy' => ['required', 'in:public,private'],
        ]);

        $validated['created_by'] = auth()->id();

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
            'activities',
            'members.user',
            'posts.user',
        ]);

        return view('community.show', compact('community'));
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