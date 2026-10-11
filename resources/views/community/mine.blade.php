@extends('layouts.site')

@section('title', 'จัดการกลุ่มของฉัน - KKU FitCom')

@section('styles')
<style>
    .cm-wrap { max-width: 900px; margin: 0 auto; }
    .cm-card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 16px; }
    .row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eee; flex-wrap: wrap; }
    .row:last-child { border-bottom: none; }
    .row a.name { font-weight: bold; color: inherit; text-decoration: none; }
    .btn { display: inline-block; padding: 4px 12px; border-radius: 8px; background: #70c5d3; color: #fff; text-decoration: none; font-size: 13px; }
    .btn.outline { background: #fff; color: #3a9fb0; border: 1px solid #70c5d3; }
    .cm-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 12px; background: #e6f6f9; color: #3a9fb0; }
    .cm-badge.warn { background: #fff4e0; color: #b9770e; }
    .muted { color: #888; font-size: 13px; }
</style>
@endsection

@section('content')
<div class="cm-wrap">

    <a href="{{ route('community.index') }}" class="muted">← กลับไปหน้ารวม Community</a>
    <h1>จัดการกลุ่มของฉัน</h1>

    {{-- ===== ส่วนที่ 1: กลุ่มที่ฉันเป็นเจ้าของ ===== --}}
    <div class="cm-card">
        <h2> กลุ่มที่ฉันเป็นเจ้าของ ({{ $ownedCommunities->count() }})</h2>

        @forelse ($ownedCommunities as $community)
            <div class="row">
                <div>
                    <a href="{{ route('community.show', $community) }}" class="name">{{ $community->name }}</a>
                    {{-- ตัวเลขทั้งสองมาจาก withCount ใน Controller --}}
                    <div class="muted">👥 สมาชิก {{ $community->approved_members_count }} คน</div>
                </div>

                <div>
                    {{-- มีคำขอค้างอยู่ → แสดงป้ายสีส้มเตือน --}}
                    @if ($community->pending_members_count > 0)
                        <span class="cm-badge warn"> รออนุมัติ {{ $community->pending_members_count }} คน</span>
                    @endif

                    {{-- ปุ่มอนุมัติ/ปฏิเสธอยู่ในหน้า show อยู่แล้ว จึงลิงก์ไปที่นั่น --}}
                    <a href="{{ route('community.show', $community) }}" class="btn">จัดการคำขอ</a>
                    <a href="{{ route('community.edit', $community) }}" class="btn outline"> แก้ไข</a>
                </div>
            </div>
        @empty
            <p class="muted">คุณยังไม่ได้สร้างกลุ่ม</p>
            <a href="{{ route('community.create') }}" class="btn">+ สร้าง Community</a>
        @endforelse
    </div>

    {{-- ===== ส่วนที่ 2: กลุ่มที่ฉันเข้าร่วม ===== --}}
    <div class="cm-card">
        <h2>👥 กลุ่มที่ฉันเข้าร่วม ({{ $memberships->count() }})</h2>

        {{-- $membership คือแถวในตาราง community_members, ->community คือข้อมูลกลุ่ม --}}
        @forelse ($memberships as $membership)
            <div class="row">
                <div>
                    <a href="{{ route('community.show', $membership->community) }}" class="name">
                        {{ $membership->community->name }}
                    </a>
                    <div class="muted">{{ $membership->community->sport?->name ?? 'ไม่ระบุกีฬา' }}</div>
                </div>

                {{-- สถานะของเราในกลุ่มนั้น --}}
                @if ($membership->status == 'approved')
                    <span class="cm-badge">✅ สมาชิก</span>
                @else
                    <span class="cm-badge warn"> รออนุมัติ</span>
                @endif
            </div>
        @empty
            <p class="muted">ยังไม่ได้เข้าร่วมกลุ่มไหน</p>
            <a href="{{ route('community.index') }}" class="btn outline">ค้นหากลุ่ม</a>
        @endforelse
    </div>

</div>
@endsection