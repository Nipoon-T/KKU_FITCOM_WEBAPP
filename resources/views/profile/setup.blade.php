@extends('layouts.site')

@section('content')
<style>
    .pf-wrap { max-width: 640px; margin: 24px auto; padding: 0 16px; }
    .pf-title { font-size: 24px; font-weight: bold; margin-bottom: 16px; }
    .pf-card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 16px; }
    .pf-label { display: block; font-size: 13px; color: #666; margin-bottom: 8px; }
    .pf-input { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; background: #fff; }
    .pf-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .pf-chip { display: inline-flex; align-items: center; gap: 6px; background: #f1f1f1; padding: 6px 14px; border-radius: 999px; font-size: 14px; cursor: pointer; }
    .pf-btn { padding: 10px 24px; background: #70c5d3; color: #fff; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; }
    .pf-ok { color: #2e7d32; margin-bottom: 12px; }
    .pf-avatar { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; background: #e0e0e0; display: block; margin: 0 auto 10px; cursor: pointer; }
    .pf-file { display: none; }
    .pf-upload { display: inline-block; padding: 8px 18px; background: #fff; color: #70c5d3; border: 1px solid #70c5d3; border-radius: 8px; font-size: 14px; cursor: pointer; }
    .pf-upload:hover { background: #70c5d3; color: #fff; }
    .pf-hint { font-size: 12px; color: #999; margin-top: 6px; }
</style>

<div class="pf-wrap">
    <div class="pf-title">ตั้งค่าโปรไฟล์</div>

    @if (session('status'))
        <p class="pf-ok">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @if ($errors->any())
            <div style="color: #c62828; margin-bottom: 12px;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="pf-card" style="text-align: center;">
            <label for="avatar">
                @if ($user->profile && $user->profile->avatar)
                    <img id="avatarPreview" class="pf-avatar" src="{{ asset('storage/'.$user->profile->avatar) }}" alt="รูปโปรไฟล์">
                @else
                    <img id="avatarPreview" class="pf-avatar" src="data:image/gif;base64,R0lGODlhAQABAIAAAOLi4gAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" alt="รูปโปรไฟล์">
                @endif
            </label>

            <label for="avatar" class="pf-upload">เปลี่ยนรูปโปรไฟล์</label>
            <input type="file" id="avatar" class="pf-file" accept="image/*">
            <input type="hidden" name="avatar_data" id="avatar_data">

            <p class="pf-hint">คลิกที่รูปหรือปุ่มเพื่อเลือกรูป</p>
            <p id="avatarError" style="color: #c62828; font-size: 13px;"></p>
            @error('avatar_data')
                <p style="color: #c62828; font-size: 13px;">{{ $message }}</p>
            @enderror
        </div>

        <div class="pf-card">
            <label class="pf-label" for="goal">เป้าหมายการออกกำลังกาย</label>
            @php $currentGoal = old('goal', $user->profile->goal ?? ''); @endphp
            <select name="goal" id="goal" class="pf-input">
                <option value="">-- เลือกเป้าหมาย --</option>
                <option value="ลดน้ำหนัก" {{ $currentGoal == 'ลดน้ำหนัก' ? 'selected' : '' }}>ลดน้ำหนัก</option>
                <option value="เพิ่มกล้ามเนื้อ" {{ $currentGoal == 'เพิ่มกล้ามเนื้อ' ? 'selected' : '' }}>เพิ่มกล้ามเนื้อ</option>
                <option value="สุขภาพทั่วไป" {{ $currentGoal == 'สุขภาพทั่วไป' ? 'selected' : '' }}>สุขภาพทั่วไป</option>
                <option value="เข้าสังคม/หาเพื่อน" {{ $currentGoal == 'เข้าสังคม/หาเพื่อน' ? 'selected' : '' }}>เข้าสังคม/หาเพื่อน</option>
                <option value="อื่นๆ" {{ $currentGoal == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ</option>
            </select>
        </div>

        <div class="pf-card">
            <label class="pf-label" for="skill_level">ระดับความสามารถ</label>
            @php $currentSkill = old('skill_level', $user->profile->skill_level ?? ''); @endphp
            <select name="skill_level" id="skill_level" class="pf-input">
                <option value="">-- เลือกระดับ --</option>
                <option value="เริ่มต้น" {{ $currentSkill == 'เริ่มต้น' ? 'selected' : '' }}>เริ่มต้น</option>
                <option value="ปานกลาง" {{ $currentSkill == 'ปานกลาง' ? 'selected' : '' }}>ปานกลาง</option>
                <option value="ผู้เชี่ยวชาญ" {{ $currentSkill == 'ผู้เชี่ยวชาญ' ? 'selected' : '' }}>ผู้เชี่ยวชาญ</option>
            </select>
        </div>

        <div class="pf-card">
            <label class="pf-label" for="preferred_location">พื้นที่สะดวก</label>
            @php $currentLocation = old('preferred_location', $user->profile->preferred_location ?? ''); @endphp
            <select name="preferred_location" id="preferred_location" class="pf-input">
                <option value="">-- เลือกพื้นที่ --</option>
                <option value="16.476583,102.818127" {{ $currentLocation == '16.476583,102.818127' ? 'selected' : '' }}>ใน มข.</option>
                <option value="16.469000,102.822000" {{ $currentLocation == '16.469000,102.822000' ? 'selected' : '' }}>รอบ มข. (ศิลา/กังสดาล/โนนม่วง)</option>
                <option value="16.417086,102.835127" {{ $currentLocation == '16.417086,102.835127' ? 'selected' : '' }}>ในเมืองขอนแก่น/ไกลออกไป</option>
            </select>
        </div>

        <div class="pf-card">
            <label class="pf-label" for="bio">เกี่ยวกับฉัน</label>
            <textarea name="bio" id="bio" rows="3" class="pf-input">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
        </div>

        <div class="pf-card">
            <span class="pf-label">กีฬาที่สนใจ (เลือกได้หลายอัน)</span>
            @php
                $selectedSports = $user->sports->pluck('id')->toArray();
            @endphp
            <div class="pf-chips">
                @foreach ($sports as $sport)
                    <label class="pf-chip">
                        <input type="checkbox" name="sports[]" value="{{ $sport->id }}"
                            {{ in_array($sport->id, $selectedSports) ? 'checked' : '' }}>
                        {{ $sport->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="pf-card">
            <span class="pf-label">ช่วงเวลาว่าง (คลิกเลือกได้หลายช่อง)</span>
            @php
                $days = ['0' => 'อาทิตย์', '1' => 'จันทร์', '2' => 'อังคาร', '3' => 'พุธ', '4' => 'พฤหัสบดี', '5' => 'ศุกร์', '6' => 'เสาร์'];
                $timeSlots = ['06:00-11:59', '12:00-17:59', '18:00-20:59'];
            @endphp
            <table style="border-collapse: collapse; width: 100%;">
                <tr>
                    <th style="padding: 8px;"></th>
                    @foreach ($timeSlots as $timeSlot)
                        <th style="padding: 8px; font-size: 12px; color: #666; font-weight: normal;">{{ $timeSlot }}</th>
                    @endforeach
                </tr>
                @foreach ($days as $dayNumber => $dayName)
                    <tr style="border-top: 1px solid #eee;">
                        <td style="padding: 8px; font-size: 14px;">{{ $dayName }}</td>
                        @foreach ($timeSlots as $timeSlot)
                            @php $slotKey = $dayNumber.'_'.$timeSlot; @endphp
                            <td style="padding: 8px; text-align: center;">
                                <input type="checkbox" name="availability[]" value="{{ $slotKey }}"
                                    {{ in_array($slotKey, $selectedAvailability) ? 'checked' : '' }}>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>

        <button type="submit" class="pf-btn">บันทึก</button>
    </form>
</div>

<script src="{{ asset('js/avatar.js') }}"></script>
@endsection