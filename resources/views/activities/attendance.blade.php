@extends('layouts.site')

@section('title', 'เช็คชื่อ - '.$activity->name)

@section('styles')
<style>
    .act-wrap { max-width: 600px; margin: 0 auto; padding: 0 4px; }
    .back-link { display: inline-block; margin-bottom: 12px; color: #3a9fb0; text-decoration: none; font-size: 14px; }
    .act-wrap h1 { font-size: 20px; margin-bottom: 4px; }

    .flash-ok { background: #e6f7ec; color: #2e9e5b; padding: 10px 16px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
    .flash-err { background: #fdeaea; color: #d9534f; padding: 10px 16px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
    .warn { background: #fff7e0; color: #a67c00; padding: 10px 16px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }

    .info-card { background: #fff; border-radius: 12px; padding: 14px 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 12px; font-size: 14px; color: #555; }
    .count-line { font-size: 14px; color: #444; margin-bottom: 12px; }
    .count-line strong { color: #70c5d3; font-size: 18px; }

    .p-row { background: #fff; border-radius: 12px; padding: 12px 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; }
    .p-row .checked { color: #2e9e5b; font-size: 13px; }
    .p-row .pending { color: #999; font-size: 13px; }
    .btn { padding: 6px 16px; border-radius: 8px; background: #70c5d3; color: #fff; border: none; cursor: pointer; font-size: 13px; }
    .btn:disabled { opacity: .5; cursor: default; }

    .empty { color: #777; padding: 20px; text-align: center; }
</style>
@endsection

@section('content')
<div class="act-wrap">
    <a href="{{ route('activities.mine') }}" class="back-link">← Back to My Activities</a>

    <h1>เช็คชื่อ: {{ $activity->name }}</h1>

    @if (session('success'))
        <p class="flash-ok">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p class="flash-err">{{ $errors->first() }}</p>
    @endif

    <div class="info-card">
        {{ $activity->sport->name }}
        · {{ $activity->date->format('d/m/Y') }}
        {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
        · {{ $activity->location->name }}
    </div>

    @unless ($canCheckin)
        <p class="warn">เช็คชื่อได้เฉพาะวันที่จัดกิจกรรม ({{ $activity->date->format('d/m/Y') }})</p>
    @endunless

    <p class="count-line">
        เช็คชื่อแล้ว
        <strong id="checked-count">{{ $participants->filter(fn ($p) => $p->attendance)->count() }}</strong>/{{ $participants->count() }} คน
    </p>

    @forelse ($participants as $participant)
        <div class="p-row">
            <span>{{ $participant->user->name }}</span>

            @if ($participant->attendance)
                <span class="checked">✅ เช็คชื่อแล้ว {{ $participant->attendance->checked_in_at->timezone('Asia/Bangkok')->format('H:i') }}</span>
            @elseif ($canCheckin)
                <form action="{{ route('activities.checkin', $activity) }}" method="POST" class="checkin-form">
                    @csrf
                    <input type="hidden" name="participant_id" value="{{ $participant->id }}">
                    <button type="submit" class="btn">เช็คชื่อ</button>
                </form>
            @else
                <span class="pending">ยังไม่ได้เช็คชื่อ</span>
            @endif
        </div>
    @empty
        <p class="empty">ยังไม่มีผู้ลงทะเบียน</p>
    @endforelse
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.checkin-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const button = form.querySelector('button');
            button.disabled = true;

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value,
                },
                body: new FormData(form),
            });

            if (!response.ok) {
                button.disabled = false;

                let message = 'เช็คชื่อไม่สำเร็จ ลองใหม่อีกครั้ง';
                try {
                    message = (await response.json()).message || message;
                } catch (e) {
                    // ไม่ใช่ JSON ใช้ข้อความเริ่มต้น
                }

                alert(message);
                return;
            }

            const data = await response.json();
            const span = document.createElement('span');
            span.className = 'checked';
            span.textContent = '✅ เช็คชื่อแล้ว ' + data.time;
            form.replaceWith(span);

            const counter = document.getElementById('checked-count');
            counter.textContent = Number(counter.textContent) + 1;
        });
    });
</script>
@endsection