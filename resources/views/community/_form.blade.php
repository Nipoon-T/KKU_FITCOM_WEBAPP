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

    // ตำแหน่งรูป: ค่าที่เพิ่งลาก > ค่าเดิมของกลุ่ม > กึ่งกลาง
    $coverPos = old('cover_position', $community?->cover_position ?? '50% 50%');
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

            {{-- กล่องเส้นประ: ยังไม่มีรูป = คลิกเพื่อเลือก / มีรูปแล้ว = ลากเพื่อปรับตำแหน่ง --}}
            <div class="cover-box" id="coverBox">
                <span id="coverPlaceholder" style="{{ $coverSrc ? 'display:none' : '' }}">⬆️<br>คลิกเพื่ออัปโหลดรูปภาพ</span>

                {{-- รูป preview: object-position = ตำแหน่งที่ลากไว้ --}}
                <img id="coverPreview" alt="รูปปก"
                    @if ($coverSrc) src="{{ $coverSrc }}" style="object-position: {{ $coverPos }}" @else style="display:none" @endif>

                {{-- ป้ายบอกวิธีใช้ (แสดงเมื่อมีรูป) --}}
                <span class="cover-hint" id="coverHint" style="{{ $coverSrc ? '' : 'display:none' }}">ลากรูปเพื่อปรับตำแหน่ง</span>

                <button type="button" id="coverPickBtn" class="cover-change-btn"
                    style="{{ $coverSrc ? '' : 'display:none' }}">🖼️ เปลี่ยนรูป</button>
            </div>

            {{-- ช่องเลือกไฟล์ (ซ่อน ไม่มี name) + รูปที่บีบแล้ว + ตำแหน่งรูป --}}
            <input type="file" id="cover_image" accept="image/*" style="display:none">
            <input type="hidden" id="cover_image_data" name="cover_image_data" value="{{ old('cover_image_data') }}">
            <input type="hidden" id="cover_position" name="cover_position" value="{{ $coverPos }}">

            <p class="hint">ระบบจะย่อรูปให้อัตโนมัติ</p>
            <p id="coverError" class="field-err"></p>
            @error('cover_image') <p class="field-err">{{ $message }}</p> @enderror
            @error('cover_position') <p class="field-err">{{ $message }}</p> @enderror
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
