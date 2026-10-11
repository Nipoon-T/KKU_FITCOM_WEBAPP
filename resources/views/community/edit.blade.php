@extends('layouts.site')

@section('title', 'แก้ไข ' . $community->name . ' - KKU FitCom')

@section('styles')
<style>
    .cm-wrap { max-width: 640px; margin: 0 auto; }
    .cm-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 16px; }
    .field { margin-bottom: 14px; }
    .field label { display: block; font-weight: bold; margin-bottom: 4px; }
    .field input[type="text"], .field textarea, .field select {
        width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 8px; font-family: inherit; font-size: 14px;
    }
    .field textarea { min-height: 90px; }
    .error { color: #c0392b; font-size: 13px; margin-top: 4px; }
    .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; background: #70c5d3; color: #fff; border: none; cursor: pointer; font-size: 14px; }
    .btn.danger { background: #e57373; }
    .muted { color: #888; font-size: 13px; }
    .cover { width: 100%; max-height: 220px; object-fit: cover; border-radius: 12px; margin-top: 8px; }
    #coverPreview { display: none; }
    .danger-zone { border: 1px solid #f3c1c1; }
</style>
@endsection

@section('content')
<div class="cm-wrap">

    <a href="{{ route('community.show', $community) }}" class="muted">← กลับไปหน้ากลุ่ม</a>
    <h1>แก้ไขกลุ่ม</h1>

    {{-- ===== ฟอร์มแก้ไข ===== --}}
    <div class="cm-card">
        <form action="{{ route('community.update', $community) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- บอก Laravel ว่าฟอร์มนี้คือ PUT (แก้ไข) --}}

            {{-- old('name', ค่าเดิม) = ถ้า validate ไม่ผ่านใช้ค่าที่เพิ่งกรอก ถ้าไม่มีใช้ค่าจาก DB --}}
            <div class="field">
                <label for="name">ชื่อกลุ่ม *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $community->name) }}" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="description">คำอธิบาย</label>
                <textarea id="description" name="description">{{ old('description', $community->description) }}</textarea>
                @error('description') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="sport_id">ประเภทกีฬา</label>
                <select id="sport_id" name="sport_id">
                    <option value="">-- ไม่ระบุ --</option>
                    @foreach ($sports as $sport)
                        {{-- เลือกกีฬาเดิมของกลุ่มไว้ให้ --}}
                        <option value="{{ $sport->id }}" {{ old('sport_id', $community->sport_id) == $sport->id ? 'selected' : '' }}>
                            {{ $sport->name }}
                        </option>
                    @endforeach
                </select>
                @error('sport_id') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="privacy">ความเป็นส่วนตัว *</label>
                <select id="privacy" name="privacy" required>
                    <option value="public" {{ old('privacy', $community->privacy) == 'public' ? 'selected' : '' }}>🌐 Public — ใครก็เข้าร่วมได้ทันที</option>
                    <option value="private" {{ old('privacy', $community->privacy) == 'private' ? 'selected' : '' }}>🔒 Private — ต้องรอเจ้าของกลุ่มอนุมัติ</option>
                </select>
                @error('privacy') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="cover_image">รูปปก (เลือกใหม่ถ้าต้องการเปลี่ยน)</label>

                {{-- รูปปัจจุบัน --}}
                @if ($community->cover_image)
                    <p class="muted">รูปปัจจุบัน:</p>
                    <img src="{{ asset('storage/' . $community->cover_image) }}" alt="รูปปัจจุบัน" class="cover">
                @endif

                {{-- ไม่มี name = ไฟล์จริงไม่ถูกส่ง (JS ย่อแล้วใส่ hidden input แทน) --}}
                <input type="file" id="cover_image" accept="image/*" style="margin-top:8px">
                <input type="hidden" id="cover_image_data" name="cover_image_data" value="{{ old('cover_image_data') }}">

                <div id="coverError" class="error"></div>
                @error('cover_image') <div class="error">{{ $message }}</div> @enderror

                {{-- preview รูปใหม่ --}}
                <img id="coverPreview" alt="รูปใหม่" class="cover"
                     src="{{ old('cover_image_data') }}"
                     style="{{ old('cover_image_data') ? 'display:block' : '' }}">
            </div>

            <button type="submit" class="btn">บันทึกการแก้ไข</button>
        </form>
    </div>

    {{-- ===== ลบกลุ่ม ===== --}}
    <div class="cm-card danger-zone">
        <h2>ลบกลุ่ม</h2>
        <p class="muted">สมาชิกและโพสต์ทั้งหมดจะถูกลบด้วย และกู้คืนไม่ได้</p>

        {{-- confirm() = กล่องถามยืนยันของเบราว์เซอร์ ถ้ากด Cancel ฟอร์มจะไม่ถูกส่ง --}}
        <form action="{{ route('community.destroy', $community) }}" method="POST"
              onsubmit="return confirm('ยืนยันลบกลุ่ม {{ $community->name }} ?')">
            @csrf
            @method('DELETE') {{-- บอก Laravel ว่าฟอร์มนี้คือ DELETE (ลบ) --}}
            <button type="submit" class="btn danger">ลบกลุ่มนี้</button>
        </form>
    </div>

</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/community.js') }}"></script>
@endsection