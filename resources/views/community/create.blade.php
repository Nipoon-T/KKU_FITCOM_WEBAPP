@extends('layouts.site')

@section('title', 'สร้าง Community - KKU FitCom')

{{-- โหลด CSS ร่วมของฟอร์ม --}}
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/community-form.css') }}">
@endsection

@section('content')
<div class="cm-wrap">
    <a href="{{ route('community.index') }}" class="back-link">← กลับ</a>

    <h1>สร้าง Community</h1>

    {{-- ใช้ฟอร์มร่วม: ยังไม่มีกลุ่ม (null) ส่งไป store แบบ POST --}}
    @include('community._form', [
        'community' => null,
        'action' => route('community.store'),
        'method' => 'POST',
        'submitLabel' => 'สร้าง Community',
    ])
</div>
@endsection

{{-- โหลด JS (ตัวนับตัวอักษร + ย่อรูปปก) --}}
@section('scripts')
    <script src="{{ asset('js/community.js') }}"></script>
@endsection