<!-- ฟอร์มกิจกรรมใช้ร่วมกับ create, edit -->

@if ($errors->any())
    <ul class="err-list">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form action="{{ $action }}" method="POST" class="act-form" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-card">
        <h2>1. ข้อมูลพื้นฐาน</h2>

        <div class="field">
            <label for="name">ชื่อกิจกรรม</label>
            <input type="text" id="name" name="name" maxlength="180"
                value="{{ old('name', $activity?->name) }}" data-counter="name-count" required>
            <span class="counter"><span id="name-count">0</span>/180</span>
        </div>

        <div class="field">
            <label for="description">คำอธิบายกิจกรรม</label>
            <textarea id="description" name="description" maxlength="300" rows="4"
                data-counter="description-count">{{ old('description', $activity?->description) }}</textarea>
            <span class="counter"><span id="description-count">0</span>/300</span>
        </div>

        <div class="field">
            <label>รูปปกกิจกรรม</label>
            <div class="cover-box" id="cover-box">
                @if ($activity?->cover_image)
                    <img src="{{ asset('storage/'.$activity->cover_image) }}" alt="รูปปกปัจจุบัน" id="cover-preview"
                        style="object-position: {{ old('cover_position', $activity?->cover_position ?? '50% 50%') }};">
                    <span class="cover-hint" id="cover-hint">ลากรูปเพื่อปรับตำแหน่ง</span>
                    <button type="button" id="cover-pick-btn" class="cover-change-btn">🖼️ เปลี่ยนรูป</button>
                @else
                    <span id="cover-placeholder">⬆️<br>คลิกเพื่ออัปโหลดรูปภาพ</span>
                    <img id="cover-preview" alt="" style="display:none;">
                    <span class="cover-hint" id="cover-hint" style="display:none;">ลากรูปเพื่อปรับตำแหน่ง</span>
                    <button type="button" id="cover-pick-btn" class="cover-change-btn" style="display:none;">🖼️ เปลี่ยนรูป</button>
                @endif
            </div>
            <input type="file" id="cover_image" name="cover_image" accept="image/*" style="display:none;">
            <input type="hidden" id="cover_position" name="cover_position"
                value="{{ old('cover_position', $activity?->cover_position ?? '50% 50%') }}">
            @error('cover_image') <p class="field-err">{{ $message }}</p> @enderror
            @error('cover_position') <p class="field-err">{{ $message }}</p> @enderror
        </div>

        <fieldset>
            <legend>ระดับกิจกรรม</legend>
            @foreach ($levels as $value => $label)
                <label class="pill">
                    <input type="radio" name="skill_level" value="{{ $value }}"
                        {{ (int) old('skill_level', $activity?->skill_level ?? 1) === $value ? 'checked' : '' }}>
                    {{ $label }}
                </label>
            @endforeach
        </fieldset>
    </div>

    <div class="form-card">
        <h2>2. เลือกประเภทกิจกรรมและสถานที่</h2>

        <fieldset>
            <legend>ประเภทกิจกรรม</legend>
            @foreach ($sports as $sport)
                <label class="pill">
                    <input type="radio" name="sport_id" value="{{ $sport->id }}"
                        {{ (int) old('sport_id', $activity?->sport_id) === $sport->id ? 'checked' : '' }} required>
                    {{ $sport->name }}
                </label>
            @endforeach
        </fieldset>

        <div class="field">
            <label for="location_id">พื้นที่/โซน</label>
            <select id="location_id" name="location_id" required>
                <option value="">เลือกสถานที่</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}"
                        data-sports="{{ $location->sports->pluck('id')->implode(',') }}"
                        {{ (int) old('location_id', $activity?->location_id) === $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-card">
        <h2>3. วันเวลาและสถานที่</h2>

        <div class="field-row">
            <div class="field">
                <label for="date">วันที่จัด</label>
                <input type="date" id="date" name="date"
                    value="{{ old('date', $activity?->date?->format('Y-m-d')) }}" required>
            </div>
            <div class="field">
                <label for="start_time">เวลาเริ่ม</label>
                <input type="time" id="start_time" name="start_time"
                    value="{{ old('start_time', $activity ? substr($activity->start_time, 0, 5) : '') }}" required>
            </div>
            <div class="field">
                <label for="end_time">เวลาสิ้นสุด</label>
                <input type="time" id="end_time" name="end_time"
                    value="{{ old('end_time', $activity ? substr($activity->end_time, 0, 5) : '') }}" required>
            </div>
        </div>

        <div class="field">
            <label for="max_participants">จำนวนผู้เข้าร่วมสูงสุด</label>
            <input type="number" id="max_participants" name="max_participants" min="1"
                value="{{ old('max_participants', $activity?->max_participants ?? 10) }}" required>
        </div>
    </div>

    <div class="form-card">
        <h2>4. จัดภายใต้ Community (ถ้ามี)</h2>

        <div class="field">
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
    </div>

    <button type="submit" class="btn btn-submit">{{ $submitLabel }}</button>
</form>

<script>
    document.querySelectorAll('[data-counter]').forEach((field) => {
        const counter = document.getElementById(field.dataset.counter);
        const update = () => { counter.textContent = field.value.length; };

        field.addEventListener('input', update);
        update();
    });

    const coverBox = document.getElementById('cover-box');
    const coverInput = document.getElementById('cover_image');
    const coverPreview = document.getElementById('cover-preview');
    const coverPlaceholder = document.getElementById('cover-placeholder');
    const coverHint = document.getElementById('cover-hint');
    const coverPositionInput = document.getElementById('cover_position');
    const coverPickBtn = document.getElementById('cover-pick-btn');

    let hasCover = {{ $activity?->cover_image ? 'true' : 'false' }};

    coverPickBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        coverInput.click();
    });

    coverBox.addEventListener('click', () => {
        if (!hasCover) {
            coverInput.click();
        }
    });

    coverInput.addEventListener('change', () => {
        const file = coverInput.files[0];
        if (!file) return;

        coverPreview.src = URL.createObjectURL(file);
        coverPreview.style.display = 'block';
        coverPreview.style.objectPosition = '50% 50%';
        coverPositionInput.value = '50% 50%';

        if (coverPlaceholder) coverPlaceholder.style.display = 'none';
        coverHint.style.display = 'block';
        coverPickBtn.style.display = 'block';
        hasCover = true;
    });

    function parsePosition(value) {
        const [x, y] = value.split(' ').map((v) => parseFloat(v));
        return { x: isNaN(x) ? 50 : x, y: isNaN(y) ? 50 : y };
    }

    let dragging = false;
    let startPos = { x: 50, y: 50 };
    let startPoint = { x: 0, y: 0 };

    function startDrag(clientX, clientY) {
        if (!hasCover) return;
        dragging = true;
        startPos = parsePosition(coverPositionInput.value);
        startPoint = { x: clientX, y: clientY };
    }

    function moveDrag(clientX, clientY) {
        if (!dragging) return;

        const rect = coverBox.getBoundingClientRect();
        const deltaXPct = ((clientX - startPoint.x) / rect.width) * 100;
        const deltaYPct = ((clientY - startPoint.y) / rect.height) * 100;

        const x = Math.round(Math.min(100, Math.max(0, startPos.x - deltaXPct)));
        const y = Math.round(Math.min(100, Math.max(0, startPos.y - deltaYPct)));

        coverPreview.style.objectPosition = x + '% ' + y + '%';
        coverPositionInput.value = x + '% ' + y + '%';
    }

    function endDrag() {
        dragging = false;
    }

    coverBox.addEventListener('mousedown', (e) => startDrag(e.clientX, e.clientY));
    window.addEventListener('mousemove', (e) => moveDrag(e.clientX, e.clientY));
    window.addEventListener('mouseup', endDrag);

    coverBox.addEventListener('touchstart', (e) => {
        const t = e.touches[0];
        startDrag(t.clientX, t.clientY);
    });
    coverBox.addEventListener('touchmove', (e) => {
        const t = e.touches[0];
        moveDrag(t.clientX, t.clientY);
        e.preventDefault();
    }, { passive: false });
    coverBox.addEventListener('touchend', endDrag);

    const isCreating = {{ $activity ? 'false' : 'true' }};
    const form = document.querySelector('.act-form');

    form.addEventListener('submit', (event) => {
        if (isCreating && coverInput.files.length === 0) {
            event.preventDefault();
            coverBox.style.borderColor = '#d9534f';
            alert('กรุณาเลือกรูปปกกิจกรรมก่อนสร้าง');
        }
    });
    const sportRadios = document.querySelectorAll('input[name="sport_id"]');
    const locationSelect = document.getElementById('location_id');
    const locationOptions = Array.from(locationSelect.options);

    function filterLocationsBySport() {
        const checked = document.querySelector('input[name="sport_id"]:checked');

        if (!checked) {
            locationOptions.forEach((opt) => { opt.hidden = false; });
            return;
        }

        const sportId = checked.value;
        const matches = locationOptions.filter((opt) => {
            if (!opt.value) return true;
            const tags = (opt.dataset.sports || '').split(',').filter(Boolean);
            return tags.length === 0 || tags.includes(sportId);
        });

        const realMatches = matches.filter((opt) => opt.value !== '');
        const visibleSet = realMatches.length > 0 ? matches : locationOptions;

        locationOptions.forEach((opt) => {
            opt.hidden = !visibleSet.includes(opt);
        });

        const selectedOpt = locationSelect.options[locationSelect.selectedIndex];
        if (selectedOpt && selectedOpt.hidden) {
            locationSelect.value = '';
        }
    }

    sportRadios.forEach((radio) => radio.addEventListener('change', filterLocationsBySport));
    filterLocationsBySport();
</script>