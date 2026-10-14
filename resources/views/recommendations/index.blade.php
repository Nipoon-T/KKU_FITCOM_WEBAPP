@extends('layouts.site')

@section('title', 'แนะนำสำหรับคุณ - KKU FitCom')

@section('styles')
<style>
    .act-wrap { max-width: 1000px; margin: 0 auto; padding: 0 4px; }
    .act-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
    .act-top h1 { font-size: 22px; }
    .act-sub { color: #777; font-size: 14px; margin-bottom: 16px; }
    .btn { display: inline-block; padding: 8px 16px; border-radius: 8px; background: #70c5d3; color: #fff; text-decoration: none; font-size: 14px; border: none; cursor: pointer; }
    .btn.outline { background: #fff; color: #3a9fb0; border: 1px solid #70c5d3; }

    .act-count { color: #777; font-size: 14px; margin-bottom: 12px; }
    .act-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
    .act-card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); display: flex; flex-direction: column; gap: 6px; }
    .act-card h3 { font-size: 16px; }
    .act-card p { font-size: 13px; color: #555; margin: 0; }
    .act-cover-empty { width: 100%; aspect-ratio: 16 / 9; border-radius: 8px; margin-bottom: 4px; background: #e6f6f9; }
    .act-card img { width: 100%; aspect-ratio: 16 / 9; object-fit: cover; border-radius: 8px; margin-bottom: 4px; }
    .act-tag { display: inline-block; background: #e3f4f7; color: #3a9fb0; border-radius: 20px; padding: 2px 10px; font-size: 12px; width: fit-content; }
    .act-card .btn { margin-top: 8px; align-self: flex-start; }
    .empty { color: #777; padding: 24px; text-align: center; }

    /* ส่วนที่เพิ่มสำหรับหน้าแนะนำ: ป้ายและแถบ % match */
    .match-badge { display: inline-block; border-radius: 20px; padding: 3px 12px; font-size: 13px; font-weight: bold; width: fit-content; }
    .match-badge.high { background: #e1f5e9; color: #1f8f4e; }
    .match-badge.mid { background: #fff3d6; color: #b07a00; }
    .match-badge.low { background: #f2f2f2; color: #777; }
    .match-bar { height: 6px; background: #eee; border-radius: 6px; overflow: hidden; margin-bottom: 4px; }
    .match-bar span { display: block; height: 100%; border-radius: 6px; }
    .match-bar span.high { background: #34b36b; }
    .match-bar span.mid { background: #f0b429; }
    .match-bar span.low { background: #bbb; }
    .section-title { font-size: 18px; margin: 28px 0 12px; }
</style>
@endsection

@section('content')
@php
    // ชื่อระดับกิจกรรม (ตรงกับหน้าค้นหากิจกรรม)
    $levels = [1 => 'เริ่มต้น', 2 => 'ปานกลาง', 3 => 'เชี่ยวชาญ'];
@endphp

<div class="act-wrap">
    <div class="act-top">
        <h1>แนะนำสำหรับคุณ</h1>
        <div>
            <a href="{{ route('activities.index') }}" class="btn outline">ค้นหากิจกรรมทั้งหมด</a>
        </div>
    </div>

    <p class="act-sub">เรียงตามความเหมาะสมกับกีฬา เป้าหมาย ระดับ เวลา และสถานที่ในโปรไฟล์ของคุณ</p>

    {{-- ===== ส่วนที่ 1: Community ===== --}}
    <h2 class="section-title" style="margin-top: 0;">Community ที่เหมาะกับคุณ ({{ $communities->count() }})</h2>

    <div class="act-grid">
        @forelse ($communities as $community)
            @php
                $score = (int) $community->match_score;
                $tone = $score >= 70 ? 'high' : ($score >= 40 ? 'mid' : 'low');
            @endphp

            <div class="act-card">
                @if ($community->cover_image)
                    <img src="{{ asset('storage/'.$community->cover_image) }}" alt="{{ $community->name }}"
                        style="object-position: {{ $community->cover_position ?? '50% 50%' }};">
                @else
                    <div class="act-cover-empty"></div>
                @endif

                <span class="match-badge {{ $tone }}">✨ Match {{ $score }}%</span>
                <div class="match-bar"><span class="{{ $tone }}" style="width: {{ $score }}%"></span></div>

                <h3>{{ $community->name }}</h3>

                @if ($community->description)
                    <p>{{ \Illuminate\Support\Str::limit($community->description, 100) }}</p>
                @endif

                <span class="act-tag">{{ $community->sport->name ?? '-' }} · {{ $community->privacy === 'private' ? '🔒 Private' : '🌐 Public' }}</span>
                <p>👥 สมาชิก {{ $community->approved_members_count }} คน</p>

                <a href="{{ route('community.show', $community) }}" class="btn outline">ดูรายละเอียด</a>
            </div>
        @empty
            <p class="empty">ยังไม่มี Community แนะนำในตอนนี้</p>
        @endforelse
    </div>

    {{-- ===== ส่วนที่ 2: กิจกรรม ===== --}}
    <h2 class="section-title">กิจกรรมแนะนำ ({{ $recommendations->count() }})</h2>

    <div class="act-grid">
        @forelse ($recommendations as $activity)
            @php
                $score = (int) $activity->match_score;
                $tone = $score >= 70 ? 'high' : ($score >= 40 ? 'mid' : 'low');
            @endphp

            <div class="act-card">
                @if ($activity->cover_image)
                    <img src="{{ asset('storage/'.$activity->cover_image) }}" alt="{{ $activity->name }}"
                        style="object-position: {{ $activity->cover_position ?? '50% 50%' }};">
                @else
                    <div class="act-cover-empty"></div>
                @endif

                <span class="match-badge {{ $tone }}">✨ Match {{ $score }}%</span>
                <div class="match-bar"><span class="{{ $tone }}" style="width: {{ $score }}%"></span></div>

                <h3>{{ $activity->name }}</h3>

                @if ($activity->description)
                    <p>{{ \Illuminate\Support\Str::limit($activity->description, 100) }}</p>
                @endif

                <span class="act-tag">{{ $activity->sport->name ?? '-' }} · {{ $levels[$activity->skill_level] ?? '-' }}</span>

                <p>📅 {{ \Carbon\Carbon::parse($activity->date)->format('d/m/Y') }} {{ substr($activity->start_time, 0, 5) }} น. - {{ substr($activity->end_time, 0, 5) }} น.</p>
                <p>📍 {{ $activity->location->name ?? '-' }}</p>

                @if ($activity->community)
                    <p>👥 {{ $activity->community->name }}</p>
                @endif

                <p>🙋 เข้าร่วมแล้ว: {{ $activity->registered_count ?? 0 }}/{{ $activity->max_participants }}</p>

                <a href="{{ route('activities.show', ['activity' => $activity, 'from' => 'recommendations']) }}" class="btn outline">ดูรายละเอียด</a>
            </div>
        @empty
            <p class="empty">ยังไม่มีกิจกรรมแนะนำในตอนนี้</p>
        @endforelse
    </div>
</div>
@endsection