{{-- ฟอร์มกิจกรรม ใช้ร่วมกันทั้งหน้าสร้าง (create) และแก้ไข (edit) --}}
{{-- ตัวแปรที่ต้องส่งเข้ามา: $activity (null ถ้าเป็นการสร้าง), $action, $method, $submitLabel --}}

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form action="{{ $action }}" method="POST">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <h2>1. ข้อมูลพื้นฐาน</h2>

    <div>
        <label for="name">ชื่อกิจกรรม</label>
        <input type="text" id="name" name="name" maxlength="180" size="40"
            value="{{ old('name', $activity?->name) }}" data-counter="name-count" required>
        <span><span id="name-count">0</span>/180</span>
    </div>

    <div>
        <label for="description">คำอธิบายกิจกรรม</label><br>
        <textarea id="description" name="description" maxlength="300" rows="4" cols="50"
            data-counter="description-count">{{ old('description', $activity?->description) }}</textarea>
        <span><span id="description-count">0</span>/300</span>
    </div>

    <fieldset>
        <legend>ระดับกิจกรรม</legend>
        @foreach ($levels as $value => $label)
            <label>
                <input type="radio" name="skill_level" value="{{ $value }}"
                    {{ (int) old('skill_level', $activity?->skill_level ?? 1) === $value ? 'checked' : '' }}>
                {{ $label }}
            </label>
        @endforeach
    </fieldset>

    <div>
        <label for="location_id">พื้นที่/โซน (เลือกที่มีอยู่)</label>
        <select id="location_id" name="location_id">
            <option value="">หรือกรอกสถานที่ใหม่ด้านล่าง</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}"
                    {{ (int) old('location_id', $activity?->location_id) === $location->id ? 'selected' : '' }}>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="new_location_name">สถานที่ใหม่ (ชื่อ)</label>
        <input type="text" id="new_location_name" name="new_location_name" value="{{ old('new_location_name') }}">
    </div>

    <div>
        <label for="new_location_address">สถานที่ใหม่ (ที่อยู่)</label>
        <input type="text" id="new_location_address" name="new_location_address" value="{{ old('new_location_address') }}">
    </div>

    <h2>2. เลือกประเภทกิจกรรม</h2>

    <fieldset>
        <legend>ประเภทกิจกรรม</legend>
        @foreach ($sports as $sport)
            <label>
                <input type="radio" name="sport_id" value="{{ $sport->id }}"
                    {{ (int) old('sport_id', $activity?->sport_id) === $sport->id ? 'checked' : '' }} required>
                {{ $sport->name }}
            </label>
        @endforeach
    </fieldset>

    <h2>3. วันเวลาและสถานที่</h2>

    <div>
        <label for="date">วันที่จัด</label>
        <input type="date" id="date" name="date"
            value="{{ old('date', $activity?->date?->format('Y-m-d')) }}" required>
    </div>

    <div>
        <label for="start_time">เวลาที่จัด</label>
        <input type="time" id="start_time" name="start_time"
            value="{{ old('start_time', $activity ? substr($activity->start_time, 0, 5) : '') }}" required>
        -
        <input type="time" id="end_time" name="end_time"
            value="{{ old('end_time', $activity ? substr($activity->end_time, 0, 5) : '') }}" required>
    </div>

    <div>
        <label for="max_participants">จำนวนผู้เข้าร่วมสูงสุด</label>
        <input type="number" id="max_participants" name="max_participants" min="1"
            value="{{ old('max_participants', $activity?->max_participants ?? 10) }}" required>
    </div>

    <h2>4. จัดภายใต้ Community (ถ้ามี)</h2>

    <div>
        <select id="community_id" name="community_id">
            <option value="">ไม่สังกัดกลุ่ม</option>
            @foreach ($communities as $community)
                <option value="{{ $community->id }}"
                    {{ (int) old('community_id', $activity?->community_id ?? request('community')) === $community->id ? 'selected' : '' }}>
                    {{ $community->name }}
                </option>
            @endforeach
        </select>
    </div>

    <hr>

    <button type="submit">{{ $submitLabel }}</button>
</form>

<script>
    // ตัวนับตัวอักษร (0/180, 0/300)
    document.querySelectorAll('[data-counter]').forEach((field) => {
        const counter = document.getElementById(field.dataset.counter);
        const update = () => { counter.textContent = field.value.length; };

        field.addEventListener('input', update);
        update();
    });
</script>
