{{-- ฟอร์ม Community ใช้ร่วมกันระหว่าง create และ edit --}}
{{-- ตัวแปรที่ต้องส่งมา: $community (null ตอนสร้าง), $action, $method, $submitLabel และ $sports (จาก Controller) --}}

{{-- กล่องรวม error ด้านบน --}}
@if ($errors->any())
    <ul class="err-list">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

@php
    // รูปที่จะแสดงในกล่องรูปปก: รูปที่เพิ่งเลือก (ตอนฟอร์มไม่ผ่าน) > รูปเดิมของกลุ่ม > ไม่มี
    $coverSrc = old('cover_image_data')
        ?: ($community?->cover_image ? asset('storage/'.$community->cover_image) : null);
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    {{-- หน้า edit ส่ง PUT / หน้า create ส่ง POST --}}
    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- ===== การ์ด 1: ข้อมูลพื้นฐาน ===== --}}
    <div class="form-card">
        <h2>1. ข้อมูลพื้นฐาน</h2>

        <div class="field">
            <label for="name">ชื่อกลุ่ม</label>
            {{-- data-counter = ให้ JS นับตัวอักษรไปแสดงใน id ที่ระบุ --}}
            <input type="text" id="name" name="name" maxlength="255"
                value="{{ old('name', $community?->name) }}" data-counter="name-count" required>
            <span class="counter"><span id="name-count">0</span>/255</span>
        </div>

        <div class="field">
            <label for="description">คำอธิบายกลุ่ม</label>
            <textarea id="description" name="description" maxlength="500" rows="4"
                data-counter="description-count">{{ old('description', $community?->description) }}</textarea>
            <span class="counter"><span id="description-count">0</span>/500</span>
        </div>

        <div class="field">
            <label>รูปปกกลุ่ม</label>

            {{-- กล่องเส้นประ: คลิกที่ไหนก็ได้เพื่อเลือกรูป --}}
            <div class="cover-box" id="coverBox">
                {{-- ข้อความตอนยังไม่มีรูป --}}
                <span id="coverPlaceholder" style="{{ $coverSrc ? 'display:none' : '' }}">⬆️<br>คลิกเพื่ออัปโหลดรูปภาพ</span>

                {{-- รูป preview --}}
                <img id="coverPreview" alt="รูปปก"
                    @if ($coverSrc) src="{{ $coverSrc }}" @else style="display:none" @endif>

                {{-- ปุ่มเปลี่ยนรูป (แสดงเมื่อมีรูปแล้ว) --}}
                <button type="button" id="coverPickBtn" class="cover-change-btn"
                    style="{{ $coverSrc ? '' : 'display:none' }}">🖼️ เปลี่ยนรูป</button>
            </div>

            {{-- ช่องเลือกไฟล์ถูกซ่อน และไม่มี name = ไฟล์จริงไม่ถูกส่ง (JS ย่อรูปแล้วใส่ hidden ด้านล่างแทน) --}}
            <input type="file" id="cover_image" accept="image/*" style="display:none">
            <input type="hidden" id="cover_image_data" name="cover_image_data" value="{{ old('cover_image_data') }}">

            <p class="hint">ระบบจะย่อรูปให้อัตโนมัติ</p>
            <p id="coverError" class="field-err"></p>
            @error('cover_image') <p class="field-err">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- ===== การ์ด 2: ประเภทกีฬา ===== --}}
    <div class="form-card">
        <h2>2. ประเภทกีฬา</h2>

        <fieldset>
            <legend>กลุ่มนี้เล่นกีฬาอะไร</legend>

            {{-- ตัวเลือก "ไม่ระบุ" (sport_id เป็น nullable) --}}
            <label class="pill">
                <input type="radio" name="sport_id" value=""
                    {{ old('sport_id', $community?->sport_id) == null ? 'checked' : '' }}>
                ไม่ระบุ
            </label>

            @foreach ($sports as $sport)
                <label class="pill">
                    <input type="radio" name="sport_id" value="{{ $sport->id }}"
                        {{ (int) old('sport_id', $community?->sport_id) === $sport->id ? 'checked' : '' }}>
                    {{ $sport->name }}
                </label>
            @endforeach
        </fieldset>
    </div>

    {{-- ===== การ์ด 3: ความเป็นส่วนตัว ===== --}}
    <div class="form-card">
        <h2>3. ความเป็นส่วนตัว</h2>

        <fieldset>
            <label class="pill">
                <input type="radio" name="privacy" value="public"
                    {{ old('privacy', $community?->privacy ?? 'public') === 'public' ? 'checked' : '' }}>
                🌐 Public
            </label>
            <label class="pill">
                <input type="radio" name="privacy" value="private"
                    {{ old('privacy', $community?->privacy) === 'private' ? 'checked' : '' }}>
                🔒 Private
            </label>
            <p class="hint">Public = ใครก็เข้าร่วมได้ทันที · Private = ต้องรอเจ้าของกลุ่มอนุมัติ</p>
        </fieldset>
    </div>

    <button type="submit" class="btn-submit">{{ $submitLabel }}</button>
</form>
