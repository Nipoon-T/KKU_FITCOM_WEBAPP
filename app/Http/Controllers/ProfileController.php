<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $sports = Sport::all();
        $selectedAvailability = $user->availabilities->map(fn ($a) => $a->day_of_week.'_'.$a->time_slot)->toArray();

        return view('profile.setup', [
            'user' => $user,
            'sports' => $sports,
            'selectedAvailability' => $selectedAvailability,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // รายการช่วงเวลาว่างที่ถูกต้อง เช่น "1_06:00-11:59"
        $days = ['0', '1', '2', '3', '4', '5', '6'];
        $timeSlots = ['06:00-11:59', '12:00-17:59', '18:00-20:59'];
        $allowedSlots = [];
        foreach ($days as $day) {
            foreach ($timeSlots as $timeSlot) {
                $allowedSlots[] = $day.'_'.$timeSlot;
            }
        }

        $request->validate([
            'goal' => ['nullable', Rule::in(['ลดน้ำหนัก', 'เพิ่มกล้ามเนื้อ', 'สุขภาพทั่วไป', 'เข้าสังคม/หาเพื่อน', 'อื่นๆ'])],
            'skill_level' => ['nullable', Rule::in(['เริ่มต้น', 'ปานกลาง', 'ผู้เชี่ยวชาญ'])],
            'preferred_location' => ['nullable', Rule::in([
                '16.476583,102.818127',
                '16.469000,102.822000',
                '16.417086,102.835127',
            ])],
            'bio' => ['nullable', 'string', 'max:500'],
            'sports' => ['nullable', 'array'],
            'sports.*' => ['exists:sports,id'],
            'availability' => ['nullable', 'array'],
            'availability.*' => [Rule::in($allowedSlots)],
            // Base64 ของรูป 2MB ยาวประมาณ 2.8 ล้านตัวอักษร
            'avatar_data' => ['nullable', 'string', 'max:3000000'],
        ]);

        $user = auth()->user();

        $data = $request->only(['goal', 'skill_level', 'preferred_location', 'bio']);

        // ถ้ามีรูปใหม่ → ตรวจและบันทึก
        if ($request->filled('avatar_data')) {
            $path = $this->saveAvatar($request->input('avatar_data'), $user->id);

            if ($path === null) {
                return back()
                    ->withErrors(['avatar_data' => 'รูปโปรไฟล์ไม่ถูกต้อง กรุณาเลือกรูปใหม่'])
                    ->withInput();
            }

            // ลบรูปเก่า (ถ้ามี) หลังบันทึกรูปใหม่สำเร็จแล้ว
            $oldAvatar = $user->profile->avatar ?? null;
            if ($oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }

            $data['avatar'] = $path;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        $user->sports()->sync($request->input('sports', []));

        $user->availabilities()->delete();
        foreach ($request->input('availability', []) as $slot) {
            [$day, $timeSlot] = explode('_', $slot);
            $user->availabilities()->create([
                'day_of_week' => $day,
                'time_slot' => $timeSlot,
            ]);
        }

        return redirect()->route('profile.setup')->with('status', 'บันทึกโปรไฟล์สำเร็จ');
    }

    // ตรวจและบันทึกรูปโปรไฟล์ที่ส่งมาเป็น Base64
    // คืน path ถ้าสำเร็จ / คืน null ถ้ารูปไม่ถูกต้อง
    private function saveAvatar(string $data, int $userId): ?string
    {
        $prefix = 'data:image/jpeg;base64,';

        // 1) ต้องขึ้นต้นด้วย data:image/jpeg;base64,
        if (! str_starts_with($data, $prefix)) {
            return null;
        }

        // 2) แปลงกลับเป็นไฟล์ (true = ตรวจแบบเข้มงวด)
        $binary = base64_decode(substr($data, strlen($prefix)), true);
        if ($binary === false) {
            return null;
        }

        // 3) ขนาดไฟล์ต้องไม่เกิน 2MB
        if (strlen($binary) > 2 * 1024 * 1024) {
            return null;
        }

        // 4) ต้องเป็นรูป JPEG จริง
        $info = @getimagesizefromstring($binary);
        if ($info === false || $info['mime'] !== 'image/jpeg') {
            return null;
        }

        // 5) บันทึกลง storage/app/public/avatars
        $path = 'avatars/'.$userId.'-'.Str::random(20).'.jpg';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
