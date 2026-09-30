@extends('layouts.site')

@section('title', $activity->name.' - KKU FitCom')

@section('styles')
<style>
    .act-wrap { max-width: 700px; margin: 0 auto; padding: 0 4px; }
    .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; background: #70c5d3; color: #fff; text-decoration: none; font-size: 15px; border: none; cursor: pointer; }
    .btn.outline { background: #fff; color: #3a9fb0; border: 1px solid #70c5d3; padding: 6px 14px; font-size: 13px; }
    .back-link { display: inline-block; margin-bottom: 12px; color: #3a9fb0; text-decoration: none; font-size: 14px; }

    .detail-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 16px; }
    .detail-card h1 { font-size: 20px; margin-bottom: 8px; }
    .detail-card p { margin: 6px 0; font-size: 14px; color: #444; }
    .detail-card .desc { color: #666; }
    .cover-hero { width: 100%; aspect-ratio: 16 / 9; max-height: 320px; object-fit: cover; border-radius: 12px; margin-bottom: 16px; }

    .status-box { text-align: center; padding: 16px; }
    .status-box p { font-size: 15px; font-weight: bold; color: #3a9fb0; }
    .flash-ok { background: #e6f7ec; color: #2e9e5b; padding: 10px 16px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
    .flash-err { background: #fdeaea; color: #d9534f; padding: 10px 16px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }

    .owner-box { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .owner-box .label { font-size: 13px; color: #777; margin-bottom: 8px; }
    .owner-box a { margin-right: 8px; }
</style>
@endsection

@section('content')
<div class="act-wrap">
    <a href="{{ route('activities.index') }}" class="back-link">← กลับ</a>

    @if (session('success'))
        <p class="flash-ok">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p class="flash-err">{{ $errors->first() }}</p>
    @endif
    @if ($activity->cover_image)
        <img src="{{ asset('storage/'.$activity->cover_image) }}" alt="{{ $activity->name }}" class="cover-hero"
            style="object-position: {{ $activity->cover_position ?? '50% 50%' }};">
    @endif
    <div class="detail-card">
        <h1>{{ $activity->name }}</h1>
        <p class="desc">{{ $activity->description ?: 'ไม่มีคำอธิบาย' }}</p>

        <p>📅 <strong>วันจัดกิจกรรม:</strong> {{ $activity->date->format('d/m/Y') }} {{ substr($activity->start_time, 0, 5) }} น. - {{ substr($activity->end_time, 0, 5) }} น.</p>
        <p>
            📍 <strong>สถานที่:</strong>
            {{ $activity->location->name }}
            @if ($activity->location->address)
                ({{ $activity->location->address }})
            @endif
        </p>

        <p>🏃 <strong>ประเภทกิจกรรม:</strong> {{ $activity->sport->name }}</p>
        <p>⭐ <strong>ระดับกิจกรรม:</strong> {{ $levels[$activity->skill_level] ?? '-' }}</p>

        @if ($activity->community)
            <p>
                👥 <strong>กลุ่ม:</strong>
                <a href="{{ route('community.show', $activity->community) }}">{{ $activity->community->name }}</a>
            </p>
        @endif

        <p>🙋 <strong>ผู้จัด:</strong> {{ $activity->creator->name }}</p>
        <p>👤 <strong>ผู้เข้าร่วม:</strong> <span id="participant-count">{{ $registeredCount }}</span>/{{ $activity->max_participants }}</p>
    </div>

    <div class="detail-card status-box">
        @if ($myParticipation?->status === 'registered')
            <p>✅ เข้าร่วมแล้ว</p>
        @elseif ($activity->date->lt(today()))
            <p>กิจกรรมนี้จบไปแล้ว</p>
        @elseif ($registeredCount >= $activity->max_participants)
            <p>กิจกรรมเต็มแล้ว</p>
        @else
            <form action="{{ route('activities.register', $activity) }}" method="POST" id="register-form">
                @csrf
                <button type="submit" class="btn">เข้าร่วมกิจกรรม</button>
            </form>
            <p id="register-message" class="flash-err" style="display:none;"></p>
        @endif
    </div>

    @if ((int) $activity->created_by === (int) auth()->id())
        <div class="owner-box">
            <p class="label">สำหรับผู้จัดกิจกรรม</p>
            @unless ($activity->date->lt(today()))
                <a href="{{ route('activities.edit', $activity) }}" class="btn outline">แก้ไขกิจกรรม</a>
            @endunless
            <a href="{{ route('activities.attendance', $activity) }}" class="btn outline">เช็คชื่อผู้เข้าร่วม</a>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const form = document.getElementById('register-form');

    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const message = document.getElementById('register-message');
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value,
                },
            });
            const data = await response.json();

            if (data.ok) {
                message.style.display = 'none';

                if (data.count !== null) {
                    document.getElementById('participant-count').textContent = data.count;
                }

                form.replaceWith(Object.assign(document.createElement('p'), {
                    textContent: '✅ เข้าร่วมแล้ว',
                }));
            } else {
                message.textContent = data.message;
                message.style.display = 'block';
            }
        });
    }
</script>
@endsection