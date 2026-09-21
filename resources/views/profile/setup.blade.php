<x-layouts::app :title="'ตั้งค่าโปรไฟล์'">
    <div style="max-width: 600px; margin: 40px auto; padding: 20px;">
        <h1>ตั้งค่าโปรไฟล์</h1>

        @if (session('status'))
            <p style="color: green;">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf

            <div style="margin-bottom: 15px;">
                <label for="goal">เป้าหมายการออกกำลังกาย</label><br>
                <input type="text" name="goal" id="goal" value="{{ old('goal', $user->profile->goal ?? '') }}" style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="skill_level">ระดับความสามารถ</label><br>
                <input type="text" name="skill_level" id="skill_level" value="{{ old('skill_level', $user->profile->skill_level ?? '') }}" style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="preferred_location">พื้นที่สะดวก</label><br>
                <input type="text" name="preferred_location" id="preferred_location" value="{{ old('preferred_location', $user->profile->preferred_location ?? '') }}" style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="bio">เกี่ยวกับฉัน</label><br>
                <textarea name="bio" id="bio" style="width: 100%; padding: 8px;">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
            </div>

            <button type="submit" style="padding: 10px 20px; background: #4FC3D9; color: white; border: none; border-radius: 4px;">
                บันทึก
            </button>
        </form>
    </div>
</x-layouts::app>