@extends('layouts.site')

@section('title', 'สร้าง Community - KKU FitCom')

@section('styles')
<style>
    .cm-wrap { max-width: 640px; margin: 0 auto; }
    .cm-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .field { margin-bottom: 14px; }
    .field label { display: block; font-weight: bold; margin-bottom: 4px; }
    .field input[type="text"], .field textarea, .field select {
        width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 8px; font-family: inherit; font-size: 14px;
    }
    .field textarea { min-height: 90px; }
    .error { color: #c0392b; font-size: 13px; margin-top: 4px; }
    .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; background: #70c5d3; color: #fff; border: none; cursor: pointer; font-size: 14px; }
    .muted { color: #888; font-size: 13px; }
    /* กล่อง preview รูป: ซ่อนไว้ก่อน จนกว่าจะเลือกรูป */
    #coverPreview { display: none; width: 100%; max-height: 220px; object-fit: cover; border-radius: 12px; margin-top: 8px; }
</style>
@endsection

@section('content')
<div class="cm-wrap">

    <a href="{{ route('community.index') }}" class="muted">← กลับไปหน้ารวม Community</a>
    <h1>สร้าง Community</h1>

    <div class="cm-card">
        {{-- enctype="multipart/form-data" จำเป็นเมื่อฟอร์มมีการอัปโหลดไฟล์ --}}
        <form action="{{ route('community.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ชื่อกลุ่ม --}}
            <div class="field">
                <label for="name">ชื่อกลุ่ม *</label>
                {{-- old('name') = ใส่ค่าที่เคยกรอกกลับมา ถ้า validate ไม่ผ่าน --}}
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                {{-- ถ้าช่องนี้ผิด จะแสดงข้อความ error ตรงนี้ --}}
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>

            {{-- คำอธิบาย --}}
            <div class="field">
                <label for="description">คำอธิบาย</label>
                <textarea id="description" name="description">{{ old('description') }}</textarea>
                @error('description') <div class="error">{{ $message }}</div> @enderror
            </div>

            {{-- ประเภทกีฬา (ข้อมูลจากตาราง sports ของคนที่ 1) --}}
            <div class="field">
                <label for="sport_id">ประเภทกีฬา</label>
                <select id="sport_id" name="sport_id">
                    <option value="">-- ไม่ระบุ --</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" {{ old('sport_id') == $sport->id ? 'selected' : '' }}>
                            {{ $sport->name }}
                        </option>
                    @endforeach
                </select>
                @error('sport_id') <div class="error">{{ $message }}</div> @enderror
            </div>

            {{-- ความเป็นส่วนตัว --}}
            <div class="field">
                <label for="privacy">ความเป็นส่วนตัว *</label>
                <select id="privacy" name="privacy" required>
                    <option value="public" {{ old('privacy') == 'public' ? 'selected' : '' }}>🌐 Public — ใครก็เข้าร่วมได้ทันที</option>
                    <option value="private" {{ old('privacy') == 'private' ? 'selected' : '' }}>🔒 Private — ต้องรอเจ้าของกลุ่มอนุมัติ</option>
                </select>
                @error('privacy') <div class="error">{{ $message }}</div> @enderror
            </div>

            {{-- รูปปก + preview --}}
            <div class="field">
                <label for="cover_image">รูปปก (ระบบจะย่อรูปให้อัตโนมัติ)</label>

                {{-- ไม่มี name = ไฟล์จริงจะไม่ถูกส่งไป server (JS จะย่อแล้วใส่ใน hidden input แทน) --}}
                <input type="file" id="cover_image" accept="image/*">

                {{-- รูปที่บีบแล้ว (ข้อความ Base64) ถูกส่งไปกับฟอร์มผ่านช่องนี้ --}}
                {{-- old() = ถ้าฟอร์มไม่ผ่าน validate รูปที่เลือกไว้จะไม่หาย --}}
                <input type="hidden" id="cover_image_data" name="cover_image_data" value="{{ old('cover_image_data') }}">

                {{-- error จาก JS (เช่น เลือกไฟล์ที่ไม่ใช่รูป) --}}
                <div id="coverError" class="error"></div>
                {{-- error จาก Controller (รูปไม่ผ่านการตรวจ) --}}
                @error('cover_image') <div class="error">{{ $message }}</div> @enderror

                {{-- preview: ถ้ามีรูปเดิมจาก old() ให้แสดงเลย --}}
                <img id="coverPreview" alt="ตัวอย่างรูปปก"
                     src="{{ old('cover_image_data') }}"
                     style="{{ old('cover_image_data') ? 'display:block' : '' }}">
            </div>

            <button type="submit" class="btn">สร้าง Community</button>
        </form>
    </div>

</div>
@endsection

{{-- โหลด JS ของ community (มีโค้ดย่อรูป + preview อยู่ในนั้นแล้ว) --}}
@section('scripts')
    <script src="{{ asset('js/community.js') }}"></script>
@endsection