{{-- ใช้ layout หลักของทีม --}}
@extends('layouts.site')

@section('title', 'Community - KKU FitCom')

{{-- CSS เฉพาะหน้านี้ --}}
@section('styles')
<style>
    .cm-wrap { max-width: 1000px; margin: 0 auto; }
    .cm-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .btn { display: inline-block; padding: 8px 16px; border-radius: 8px; background: #70c5d3; color: #fff; text-decoration: none; font-size: 14px; }
    .btn.outline { background: #fff; color: #3a9fb0; border: 1px solid #70c5d3; }
    .alert { padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; background: #e8f8ee; color: #1e7e46; }

    /* การ์ดเรียงเป็นตาราง ปรับจำนวนคอลัมน์ตามความกว้างจอเอง */
    .cm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
    .cm-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); text-decoration: none; color: inherit; }
    .cm-cover { width: 100%; height: 140px; object-fit: cover; background: #e6f6f9; display: block; }
    .cm-body { padding: 12px; }
    .cm-body h2 { font-size: 18px; margin-bottom: 6px; }
    .cm-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 12px; background: #e6f6f9; color: #3a9fb0; }
    .cm-badge.private { background: #fdecec; color: #c0392b; }
    .muted { color: #888; font-size: 13px; margin-top: 6px; }
</style>
@endsection

@section('content')
<div class="cm-wrap">

    {{-- หัวหน้า + ปุ่มสร้างกลุ่ม --}}
    <div class="cm-top">
        <h1>Community</h1>
        {{-- ห่อปุ่ม 2 ปุ่มไว้ด้วยกัน ให้อยู่ชิดขวา --}}
        <div>
            <a href="{{ route('community.mine') }}" class="btn outline">⚙️ จัดการกลุ่มของฉัน</a>
            <a href="{{ route('community.create') }}" class="btn">+ สร้าง Community</a>
        </div>
    </div>

    {{-- ข้อความแจ้งผล --}}
    @if (session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    <div class="cm-grid">
        {{-- วนลูปแสดงทุกกลุ่ม ถ้าไม่มีเลยจะไปที่ @empty --}}
        @forelse ($communities as $community)
            {{-- ทั้งการ์ดเป็นลิงก์ไปหน้า show --}}
            <a href="{{ route('community.show', $community) }}" class="cm-card">

                {{-- รูปปก: มีรูปก็แสดง ไม่มีก็แสดงกล่องสีพื้นแทน --}}
                @if ($community->cover_image)
                    <img src="{{ asset('storage/' . $community->cover_image) }}" alt="{{ $community->name }}" class="cm-cover">
                @else
                    <div class="cm-cover"></div>
                @endif

                <div class="cm-body">
                    <h2>{{ $community->name }}</h2>

                    <span class="cm-badge {{ $community->privacy == 'private' ? 'private' : '' }}">
                        {{ $community->privacy == 'private' ? '🔒 Private' : '🌐 Public' }}
                    </span>
                    <span class="cm-badge">{{ $community->sport?->name ?? 'ไม่ระบุกีฬา' }}</span>

                    {{-- approved_members_count มาจาก withCount('approvedMembers') ใน Controller --}}
                    <p class="muted">👥 สมาชิก {{ $community->approved_members_count }} คน</p>
                </div>
            </a>
        @empty
            <p class="muted">ยังไม่มี Community — ลองสร้างกลุ่มแรกเลย!</p>
        @endforelse
    </div>

</div>
@endsection