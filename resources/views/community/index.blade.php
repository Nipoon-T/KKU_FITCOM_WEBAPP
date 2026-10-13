@extends('layouts.site')

@section('title', 'Community - KKU FitCom')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/community.css') }}">
@endsection

@section('content')
<div class="cm-wrap">

    {{-- หัวหน้า + ปุ่ม (เหมือนหน้าค้นหากิจกรรม) --}}
    <div class="cm-top">
        <h1>Community</h1>
        <div>
            <a href="{{ route('community.mine') }}" class="btn outline">กลุ่มของฉัน</a>
            <a href="{{ route('community.create') }}" class="btn">+ สร้าง Community</a>
        </div>
    </div>

    @if (session('success'))
        <p class="flash-ok">{{ session('success') }}</p>
    @endif

    {{-- ===== ค้นหา/กรอง (GET: ค่าต่อท้าย URL) ===== --}}
    <form method="GET" action="{{ route('community.index') }}" id="filter-form" class="filter-card">
        {{-- พิมพ์แล้วกด Enter เพื่อค้นหา --}}
        <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 ค้นหาชื่อกลุ่ม แล้วกด Enter" class="search-box">

        <div class="filter-row">
            <fieldset>
                <legend>ประเภทกีฬา</legend>
                <label class="pill">
                    <input type="radio" name="sport_id" value="" {{ request('sport_id') == '' ? 'checked' : '' }}>
                    ทั้งหมด
                </label>
                @foreach ($sports as $sport)
                    <label class="pill">
                        <input type="radio" name="sport_id" value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'checked' : '' }}>
                        {{ $sport->name }}
                    </label>
                @endforeach
            </fieldset>

            <fieldset>
                <legend>ความเป็นส่วนตัว</legend>
                <label class="pill">
                    <input type="radio" name="privacy" value="" {{ request('privacy') == '' ? 'checked' : '' }}>
                    ทั้งหมด
                </label>
                <label class="pill">
                    <input type="radio" name="privacy" value="public" {{ request('privacy') == 'public' ? 'checked' : '' }}>
                    🌐 Public
                </label>
                <label class="pill">
                    <input type="radio" name="privacy" value="private" {{ request('privacy') == 'private' ? 'checked' : '' }}>
                    🔒 Private
                </label>
            </fieldset>
        </div>
    </form>

    <p class="cm-count">กลุ่มที่พบ ({{ $communities->count() }})</p>

    {{-- ===== การ์ดกลุ่ม (เหมือนการ์ดกิจกรรม) ===== --}}
    <div class="cm-grid">
        @forelse ($communities as $community)
            <div class="cm-card">
                @if ($community->cover_image)
                    <img src="{{ asset('storage/'.$community->cover_image) }}" alt="{{ $community->name }}"
                        style="object-position: {{ $community->cover_position ?? '50% 50%' }};">
                @endif

                <h3>{{ $community->name }}</h3>

                {{-- คำอธิบายย่อ ไม่เกิน 100 ตัวอักษร --}}
                @if ($community->description)
                    <p>{{ \Illuminate\Support\Str::limit($community->description, 100) }}</p>
                @endif

                <span class="cm-tag {{ $community->privacy === 'private' ? 'private' : '' }}">
                    {{ $community->sport?->name ?? 'ไม่ระบุกีฬา' }} · {{ $community->privacy === 'private' ? '🔒 Private' : '🌐 Public' }}
                </span>

                <p>👥 สมาชิก {{ $community->approved_members_count }} คน</p>

                <a href="{{ route('community.show', $community) }}" class="btn outline">ดูรายละเอียด</a>
            </div>
        @empty
            @if (request('q') || request('sport_id') || request('privacy'))
                <p class="empty">ไม่พบกลุ่มที่ตรงกับการค้นหา</p>
            @else
                <p class="empty">ยังไม่มี Community — ลองสร้างกลุ่มแรกเลย!</p>
            @endif
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
    // กด pill (กีฬา / ความเป็นส่วนตัว) แล้วค้นหาทันที ไม่ต้องกดปุ่ม
    const filterForm = document.getElementById('filter-form');

    filterForm.querySelectorAll('input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            filterForm.submit();
        });
    });
    // ช่องพิมพ์: กด Enter แล้วฟอร์มส่งเองอยู่แล้ว (พฤติกรรมปกติของฟอร์ม)
</script>
@endsection