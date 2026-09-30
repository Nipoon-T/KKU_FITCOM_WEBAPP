@extends('layouts.site')

@section('content')
<div style="max-width: 600px; margin: 40px auto; padding: 20px;">
    <h1>ตั้งค่าโปรไฟล์</h1>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf

        <div style="margin-bottom: 15px;">
            <label for="goal">เป้าหมายการออกกำลังกาย</label><br>
            @php $currentGoal = old('goal', $user->profile->goal ?? ''); @endphp
            <select name="goal" id="goal" style="width: 100%; padding: 8px;">
                <option value="">-- เลือกเป้าหมาย --</option>
                <option value="ลดน้ำหนัก" {{ $currentGoal == 'ลดน้ำหนัก' ? 'selected' : '' }}>ลดน้ำหนัก</option>
                <option value="เพิ่มกล้ามเนื้อ" {{ $currentGoal == 'เพิ่มกล้ามเนื้อ' ? 'selected' : '' }}>เพิ่มกล้ามเนื้อ</option>
                <option value="สุขภาพทั่วไป" {{ $currentGoal == 'สุขภาพทั่วไป' ? 'selected' : '' }}>สุขภาพทั่วไป</option>
                <option value="เข้าสังคม/หาเพื่อน" {{ $currentGoal == 'เข้าสังคม/หาเพื่อน' ? 'selected' : '' }}>เข้าสังคม/หาเพื่อน</option>
                <option value="อื่นๆ" {{ $currentGoal == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ</option>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="skill_level">ระดับความสามารถ</label><br>
            @php $currentSkill = old('skill_level', $user->profile->skill_level ?? ''); @endphp
            <select name="skill_level" id="skill_level" style="width: 100%; padding: 8px;">
                <option value="">-- เลือกระดับ --</option>
                <option value="เริ่มต้น" {{ $currentSkill == 'เริ่มต้น' ? 'selected' : '' }}>เริ่มต้น</option>
                <option value="ปานกลาง" {{ $currentSkill == 'ปานกลาง' ? 'selected' : '' }}>ปานกลาง</option>
                <option value="ผู้เชี่ยวชาญ" {{ $currentSkill == 'ผู้เชี่ยวชาญ' ? 'selected' : '' }}>ผู้เชี่ยวชาญ</option>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
        <label for="preferred_location">พื้นที่สะดวก</label><br>
        @php $currentLocation = old('preferred_location', $user->profile->preferred_location ?? ''); @endphp
        <select name="preferred_location" id="preferred_location" style="width: 100%; padding: 8px;">
            <option value="">-- เลือกพื้นที่ --</option>
            <option value="16.476583,102.818127" {{ $currentLocation == '16.476583,102.818127' ? 'selected' : '' }}>ใน มข.</option>
            <option value="16.469000,102.822000" {{ $currentLocation == '16.469000,102.822000' ? 'selected' : '' }}>รอบ มข. (ศิลา/กังสดาล/โนนม่วง)</option>
            <option value="16.417086,102.835127" {{ $currentLocation == '16.417086,102.835127' ? 'selected' : '' }}>ในเมืองขอนแก่น/ไกลออกไป</option>
        </select>
    </div>

        <div style="margin-bottom: 15px;">
            <label for="bio">เกี่ยวกับฉัน</label><br>
            <textarea name="bio" id="bio" style="width: 100%; padding: 8px;">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
        </div>

        <div style="margin-bottom: 15px;">
            <label>กีฬาที่สนใจ (เลือกได้หลายอัน)</label><br>
            @php
                $selectedSports = $user->sports->pluck('id')->toArray();
            @endphp
            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                @foreach ($sports as $sport)
                    <label style="border: 1px solid #ccc; padding: 6px 12px; border-radius: 20px; cursor: pointer;">
                        <input type="checkbox" name="sports[]" value="{{ $sport->id }}"
                            {{ in_array($sport->id, $selectedSports) ? 'checked' : '' }}
                            style="margin-right: 5px;">
                        {{ $sport->name }}
                    </label>
                @endforeach
            </div>
        </div>
        <div style="margin-bottom: 15px;">
    <label>ช่วงเวลาว่าง (คลิกเลือกได้หลายช่อง)</label><br>
    @php
        $days = ['0' => 'อาทิตย์', '1' => 'จันทร์', '2' => 'อังคาร', '3' => 'พุธ', '4' => 'พฤหัสบดี', '5' => 'ศุกร์', '6' => 'เสาร์'];
        $timeSlots = ['06:00-11:59', '12:00-17:59', '18:00-20:59'];
    @endphp
    <table style="border-collapse: collapse; width: 100%; margin-top: 8px;">
        <tr>
            <th style="border: 1px solid #ccc; padding: 8px;"></th>
            @foreach ($timeSlots as $timeSlot)
                <th style="border: 1px solid #ccc; padding: 8px;">{{ $timeSlot }}</th>
            @endforeach
        </tr>
        @foreach ($days as $dayNumber => $dayName)
            <tr>
                <td style="border: 1px solid #ccc; padding: 8px;">{{ $dayName }}</td>
                @foreach ($timeSlots as $timeSlot)
                    @php $slotKey = $dayNumber.'_'.$timeSlot; @endphp
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                        <input type="checkbox" name="availability[]" value="{{ $slotKey }}"
                            {{ in_array($slotKey, $selectedAvailability) ? 'checked' : '' }}>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>
        <button type="submit" style="padding: 10px 20px; background: #4FC3D9; color: white; border: none; border-radius: 4px;">
            บันทึก
        </button>
    </form>
</div>
@endsection