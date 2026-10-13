@extends('layouts.site')

@section('title', 'แก้ไข ' . $community->name . ' - KKU FitCom')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/community-form.css') }}">
@endsection

@section('content')
<div class="cm-wrap">
    <a href="{{ route('community.show', $community) }}" class="back-link">← กลับ</a>

    <h1>แก้ไขกลุ่ม</h1>

    {{-- ใช้ฟอร์มร่วม: ส่งข้อมูลกลุ่มเดิมไป และส่งแบบ PUT ไปที่ update --}}
    @include('community._form', [
        'community' => $community,
        'action' => route('community.update', $community),
        'method' => 'PUT',
        'submitLabel' => 'บันทึกการแก้ไข',
    ])

    {{-- ===== ลบกลุ่ม ===== --}}
    <div class="form-card danger-zone" style="margin-top:24px">
        <h2>ลบกลุ่ม</h2>
        <p class="hint" style="margin-bottom:12px">สมาชิกและโพสต์ทั้งหมดจะถูกลบด้วย และกู้คืนไม่ได้</p>

        <form action="{{ route('community.destroy', $community) }}" method="POST"
              onsubmit="return confirm('ยืนยันลบกลุ่ม {{ $community->name }} ?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger">ลบกลุ่มนี้</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/community.js') }}"></script>
@endsection