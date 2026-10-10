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

    /* กล่องค้นหา/กรอง */
    .filter { display: flex; flex-wrap: wrap; gap: 8px; background: #fff; padding: 12px; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 16px; }
    .filter input, .filter select { padding: 8px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; font-family: inherit; }
    .filter input { flex: 1; min-width: 180px; } /* ช่องค้นหายืดเต็มที่เหลือ */
    .filter button { border: none; cursor: pointer; }

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
    {{-- ===== ฟอร์มค้นหา/กรอง (GET = ค่าจะต่อท้าย URL) ===== --}}
    <form method="GET" action="{{ route('community.index') }}" class="filter">
        {{-- request('q') = ค่าที่ค้นหาอยู่ เพื่อให้ช่องยังจำคำเดิมหลัง reload --}}
        <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 ค้นหาชื่อกลุ่ม...">

        <select name="sport_id">
            <option value="">ทุกกีฬา</option>
            @foreach ($sports as $sport)
                <option value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'selected' : '' }}>
                    {{ $sport->name }}
                </option>
            @endforeach
        </select>

        <select name="privacy">
            <option value="">ทุกประเภท</option>
            <option value="public" {{ request('privacy') == 'public' ? 'selected' : '' }}>🌐 Public</option>
            <option value="private" {{ request('privacy') == 'private' ? 'selected' : '' }}>🔒 Private</option>
        </select>

        <button type="submit" class="btn">ค้นหา</button>
        {{-- ล้างตัวกรอง = กลับไปหน้าเดิมที่ไม่มีค่าใน URL --}}
        <a href="{{ route('community.index') }}" class="btn outline">ล้าง</a>
    </form>
    
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
            {{-- ถ้ากำลังค้นหาอยู่ แสดงข้อความต่างจากตอนยังไม่มีกลุ่มเลย --}}
            @if (request('q') || request('sport_id') || request('privacy'))
                <p class="muted">ไม่พบกลุ่มที่ตรงกับการค้นหา</p>
            @else
                <p class="muted">ยังไม่มี Community — ลองสร้างกลุ่มแรกเลย!</p>
            @endif
            @endforelse
    </div>

</div>
@endsection