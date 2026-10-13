@extends('layouts.site')

@section('title', 'กลุ่มของฉัน - KKU FitCom')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/community.css') }}">
@endsection

@section('content')
<div class="cm-wrap medium">
    <a href="{{ route('community.index') }}" class="back-link">← กลับ</a>

    <div class="cm-top">
        <h1>กลุ่มของฉัน</h1>
        <a href="{{ route('community.create') }}" class="btn">+ สร้าง Community</a>
    </div>

    @if (session('success'))
        <p class="flash-ok">{{ session('success') }}</p>
    @endif

    {{-- ===== ส่วนที่ 1: กลุ่มที่ฉันเป็นเจ้าของ ===== --}}
    <h2 class="sec-title">กลุ่มที่ฉันเป็นเจ้าของ ({{ $ownedCommunities->count() }})</h2>

    @forelse ($ownedCommunities as $community)
        <div class="row-card">
            <div class="name">
                <a href="{{ route('community.show', $community) }}">{{ $community->name }} →</a>
                <span class="sub">👥 {{ $community->approved_members_count }} คน</span>
            </div>

            <div class="actions">
                {{-- มีคำขอค้าง → ป้ายสีส้ม --}}
                @if ($community->pending_members_count > 0)
                    <span class="cm-tag warn">🔔 รออนุมัติ {{ $community->pending_members_count }} คน</span>
                @endif
                <a href="{{ route('community.manage', $community) }}" class="btn outline small">จัดการกลุ่ม</a>
                <a href="{{ route('community.edit', $community) }}" class="btn outline small">แก้ไข</a>
            </div>
        </div>
    @empty
        <p class="empty">คุณยังไม่ได้สร้างกลุ่ม</p>
    @endforelse

    {{-- ===== ส่วนที่ 2: กลุ่มที่ฉันเข้าร่วม ===== --}}
    <h2 class="sec-title">กลุ่มที่ฉันเข้าร่วม ({{ $memberships->count() }})</h2>

    @forelse ($memberships as $membership)
        <div class="row-card">
            <div class="name">
                <a href="{{ route('community.show', $membership->community) }}">{{ $membership->community->name }} →</a>
                <span class="sub">{{ $membership->community->sport?->name ?? 'ไม่ระบุกีฬา' }}</span>
            </div>

            <div class="actions">
                @if ($membership->status === 'approved')
                    <span class="cm-tag">✅ สมาชิก</span>
                @else
                    <span class="cm-tag warn">⏳ รออนุมัติ</span>
                @endif
            </div>
        </div>
    @empty
        <p class="empty">ยังไม่ได้เข้าร่วมกลุ่มไหน
            <br><a href="{{ route('community.index') }}" class="btn outline small" style="margin-top:8px">ค้นหากลุ่ม</a>
        </p>
    @endforelse
</div>
@endsection