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

    <script>
        // เลือกฟอร์มสร้างคอมมู (อยู่ในกล่อง .cm-wrap กันไปชนฟอร์มอื่นในเมนู เช่น ออกจากระบบ)
        const createForm = document.querySelector('.cm-wrap form');

        // 1) ตอนกดสร้าง: จำไว้ว่าส่งฟอร์มแล้ว (เก็บใน sessionStorage ของแท็บนี้)
        createForm.addEventListener('submit', () => {
            try {
                sessionStorage.setItem('communityCreated', '1');
            } catch (e) {
                // ถ้าเบราว์เซอร์ไม่ให้เก็บค่า ก็ข้ามไป ไม่ให้หน้าพัง
            }
        });

        // 2) ตอนหน้านี้ถูกเปิดกลับมา (กดปุ่มย้อนกลับ)
        window.addEventListener('pageshow', (e) => {
            const nav = performance.getEntriesByType('navigation')[0];
            const cameBack = e.persisted || nav?.type === 'back_forward';

            let created = false;
            try {
                created = sessionStorage.getItem('communityCreated') === '1';
            } catch (err) {
                created = false;
            }

            if (cameBack && created) {
                // ล้างค่าที่จำไว้ แล้วไปหน้ารวมคอมมู (replace = ไม่เหลือหน้าฟอร์มในประวัติ)
                sessionStorage.removeItem('communityCreated');
                location.replace('{{ route('community.index') }}');
            } else if (!cameBack) {
                // เข้าหน้านี้ใหม่ตามปกติ: ล้างค่าเก่าทิ้ง กันเด้งผิดครั้งต่อไป
                try {
                    sessionStorage.removeItem('communityCreated');
                } catch (err) {
                    // ไม่ต้องทำอะไร
                }
            }
        });
    </script>
@endsection