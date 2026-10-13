@extends('layouts.site')

@section('title', 'จัดการกลุ่ม '.$community->name.' - KKU FitCom')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/community.css') }}">
@endsection

@section('content')
<div class="cm-wrap medium">
    <a href="{{ route('community.mine') }}" class="back-link">← กลับ</a>

    <div class="cm-top">
        <h1>จัดการกลุ่มของคุณ: {{ $community->name }}</h1>
        <div>
            <a href="{{ route('community.show', $community) }}" class="btn outline small">ดูหน้ากลุ่ม</a>
            <a href="{{ route('community.edit', $community) }}" class="btn outline small">แก้ไขกลุ่ม</a>
        </div>
    </div>

    {{-- ===== 1. จำนวนสมาชิก ===== --}}
    <div class="stat-card">
        <p class="stat-label">👥 จำนวนสมาชิก</p>
        <p class="stat-number">{{ $members->count() }}</p>
        <p class="muted">คน</p>

        {{-- มีคำขอค้าง → ป้ายส้ม กดไปหน้าอนุมัติ --}}
        @if ($pendingCount > 0)
            <a href="{{ route('community.show', $community) }}" class="cm-tag warn">🔔 รออนุมัติ {{ $pendingCount }} คน →</a>
        @endif
    </div>

    {{-- ===== 2. สถิติกิจกรรม ===== --}}
    <div class="stat-card">
        <p class="stat-label">📊 กิจกรรมทั้งหมดตั้งแต่เปิดกลุ่ม</p>
        <p class="stat-number">{{ $totalActivities }}</p>
        <div class="stat-sub">
            <span>✅ จัดไปแล้ว {{ $pastActivities }}</span>
            <span>📅 กำลังจะมา {{ $upcoming->count() }}</span>
        </div>
        <p class="muted" style="margin-top:8px">
            เปิดกลุ่มเมื่อ {{ $community->created_at->locale('th')->translatedFormat('j F') }} พ.ศ. {{ $community->created_at->year + 543 }}
        </p>
    </div>

    {{-- ===== 3. อันดับคะแนนสมาชิก ===== --}}
    <div class="detail-card">
        <h2>🏆 อันดับคะแนนสมาชิก</h2>

        {{-- $i เริ่มที่ 0 จึง +1 เป็นอันดับ / rank-1..3 = สีเหรียญทอง เงิน ทองแดง --}}
        @forelse ($leaderboard as $i => $row)
            <div class="rank-row">
                <span class="rank-no rank-{{ $i + 1 }}">{{ $i + 1 }}</span>
                @include('community._avatar', ['user' => $row['user'], 'size' => 36])
                <span class="rank-name">{{ $row['user']->name }}</span>
                <span class="rank-points">{{ number_format($row['points']) }} คะแนน</span>
            </div>
        @empty
            <p class="muted">ยังไม่มีสมาชิก</p>
        @endforelse

        <p class="muted" style="margin-top:10px">* นับเฉพาะคะแนนที่ได้จากกิจกรรมของกลุ่มนี้</p>
    </div>

    {{-- ===== 4. กิจกรรมที่กำลังจะมาถึง ===== --}}
    <div class="detail-card">
        <h2>📅 กิจกรรมที่กำลังจะมาถึง</h2>

        @forelse ($upcoming as $i => $activity)
            {{-- รายการที่ 6 เป็นต้นไป ซ่อนไว้ก่อน (data-extra) รอกด "ดูเพิ่มเติม" --}}
            <div class="list-row" @if ($i >= 5) data-extra style="display:none" @endif>
                <a href="{{ route('activities.show', $activity) }}">{{ $activity->name }} →</a>
                <span class="muted">
                    📅 {{ $activity->date->locale('th')->translatedFormat('j M') }} {{ $activity->date->year + 543 }}
                    · {{ substr($activity->start_time, 0, 5) }} น.
                </span>
            </div>
        @empty
            <p class="muted">ยังไม่มีกิจกรรมที่กำลังจะมาถึง</p>
        @endforelse

        @if ($upcoming->count() > 5)
            <button type="button" id="showMoreBtn" class="btn outline small" style="margin-top:10px">
                ดูเพิ่มเติม ({{ $upcoming->count() - 5 }})
            </button>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // กด "ดูเพิ่มเติม" → แสดงรายการที่ซ่อนไว้ทั้งหมด แล้วซ่อนปุ่ม
    const showMoreBtn = document.getElementById('showMoreBtn');

    if (showMoreBtn) {
        showMoreBtn.addEventListener('click', function () {
            document.querySelectorAll('[data-extra]').forEach(function (row) {
                row.style.display = 'flex';
            });
            showMoreBtn.style.display = 'none';
        });
    }
</script>
@endsection
