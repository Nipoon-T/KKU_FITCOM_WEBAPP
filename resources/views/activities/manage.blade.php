@extends('layouts.site')

@section('title', 'จัดการกิจกรรมของคุณ - KKU FitCom')

@section('styles')
<style>
    .act-wrap { max-width: 800px; margin: 0 auto; padding: 0 4px; }
    .act-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
    .act-top h1 { font-size: 20px; }
    .back-link { display: inline-block; margin-bottom: 12px; color: #3a9fb0; text-decoration: none; font-size: 14px; }
    .btn { display: inline-block; padding: 8px 16px; border-radius: 8px; background: #70c5d3; color: #fff; text-decoration: none; font-size: 14px; border: none; cursor: pointer; }
    .btn.outline { background: #fff; color: #3a9fb0; border: 1px solid #70c5d3; }
    .btn.danger { background: #fff; color: #d9534f; border: 1px solid #d9534f; padding: 6px 14px; font-size: 13px; }

    .flash-ok { background: #e6f7ec; color: #2e9e5b; padding: 10px 16px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
    .flash-err { background: #fdeaea; color: #d9534f; padding: 10px 16px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }

    .sec-title { font-size: 16px; margin: 24px 0 12px; color: #3a9fb0; }

    .row-card { background: #fff; border-radius: 12px; padding: 14px 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .row-card .name a { color: #222; text-decoration: none; font-weight: bold; }
    .row-card .name a:hover { color: #3a9fb0; }
    .row-card .date { font-size: 13px; color: #999; margin-left: 6px; }
    .row-card .actions { display: flex; gap: 8px; align-items: center; }

    .checkin-card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 10px; }
    .checkin-card h3 { font-size: 15px; margin-bottom: 4px; }
    .checkin-card p { font-size: 13px; color: #666; margin: 4px 0; }
    progress { width: 100%; height: 8px; border-radius: 8px; accent-color: #70c5d3; margin: 6px 0; }

    .empty { color: #777; padding: 20px; text-align: center; }
</style>
@endsection

@section('content')
<div class="act-wrap">
    <a href="{{ route('activities.index') }}" class="back-link">← กลับ</a>

    <div class="act-top">
        <h1>จัดการกิจกรรมของคุณ</h1>
        <a href="{{ route('activities.create') }}" class="btn">+ สร้างกิจกรรม</a>
    </div>

    @if (session('success'))
        <p class="flash-ok">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p class="flash-err">{{ $errors->first() }}</p>
    @endif

    <h2 class="sec-title">แก้ไข/ยกเลิกกิจกรรม</h2>

    @forelse ($activities as $activity)
        <div class="row-card">
            <div class="name">
                <a href="{{ route('activities.show', $activity) }}">{{ $activity->name }} →</a>
                <span class="date">{{ $activity->date->format('d/m/Y') }}</span>
            </div>
            <div class="actions">
                @unless ($activity->date->lt(today()))
                    <a href="{{ route('activities.edit', $activity) }}" class="btn outline">แก้ไข</a>
                @endunless
                <form action="{{ route('activities.destroy', $activity) }}" method="POST" class="delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn danger">ลบ</button>
                </form>
            </div>
        </div>
    @empty
        <p class="empty">คุณยังไม่ได้สร้างกิจกรรม</p>
    @endforelse

    <h2 class="sec-title">เช็คชื่อผู้เข้าร่วม</h2>

    @forelse ($activities as $activity)
        <div class="checkin-card">
            <h3>{{ $activity->name }}</h3>
            <p>
                {{ $activity->date->format('d/m/Y') }}
                {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
                · {{ $activity->location->name }}
            </p>

            <progress value="{{ $activity->registered_count }}" max="{{ max(1, $activity->max_participants) }}"></progress>
            <p>{{ $activity->registered_count }}/{{ $activity->max_participants }} Registered · เช็คชื่อแล้ว {{ $activity->checked_count }}/{{ $activity->registered_count }}</p>

            <a href="{{ route('activities.attendance', $activity) }}" class="btn outline">Check-in List</a>
        </div>
    @empty
        <p class="empty">ยังไม่มีกิจกรรมให้เช็คชื่อ</p>
    @endforelse
</div>
@endsection

@section('scripts')
<script>
    // ถามยืนยันก่อนลบ
    document.querySelectorAll('.delete-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!confirm('ต้องการลบกิจกรรมนี้ใช่หรือไม่?')) {
                event.preventDefault();
            }
        });
    });
</script>
@endsection