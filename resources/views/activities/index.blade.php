@extends('layouts.site')

@section('title', 'ค้นหากิจกรรม - KKU FitCom')

@section('styles')
<style>
    .act-wrap { max-width: 1000px; margin: 0 auto; padding: 0 4px; }
    .act-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
    .act-top h1 { font-size: 22px; }
    .btn { display: inline-block; padding: 8px 16px; border-radius: 8px; background: #70c5d3; color: #fff; text-decoration: none; font-size: 14px; border: none; cursor: pointer; }
    .btn.outline { background: #fff; color: #3a9fb0; border: 1px solid #70c5d3; }

    .filter-card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 20px; }
    .filter-card input[type="text"],
    .filter-card input[type="date"],
    .filter-card input[type="time"] { padding: 8px 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
    .filter-row { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 12px; }
    .filter-row fieldset { border: none; padding: 0; min-width: 160px; }
    .filter-row legend { font-size: 13px; color: #777; margin-bottom: 6px; padding: 0; }
    .filter-row label { display: inline-flex; align-items: center; gap: 4px; font-size: 14px; margin-right: 10px; white-space: nowrap; }
    .search-box { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 20px; font-size: 15px; }

    .act-count { color: #777; font-size: 14px; margin-bottom: 12px; }
    .act-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
    .act-card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); display: flex; flex-direction: column; gap: 6px; }
    .act-card h3 { font-size: 16px; }
    .act-card p { font-size: 13px; color: #555; margin: 0; }
    .act-card img { width: 100%; aspect-ratio: 16 / 9; object-fit: cover; border-radius: 8px; margin-bottom: 4px; }
    .act-tag { display: inline-block; background: #e3f4f7; color: #3a9fb0; border-radius: 20px; padding: 2px 10px; font-size: 12px; width: fit-content; }
    .act-card .btn { margin-top: 8px; align-self: flex-start; }
    .empty { color: #777; padding: 24px; text-align: center; }
</style>
@endsection

@section('content')
<div class="act-wrap">
    <div class="act-top">
        <h1>ค้นหากิจกรรม</h1>
        <div>
            <a href="{{ route('activities.mine') }}" class="btn outline">กิจกรรมของฉัน</a>
            <a href="{{ route('activities.create') }}" class="btn">+ สร้างกิจกรรม</a>
        </div>
    </div>

    <form method="GET" action="{{ route('activities.index') }}" id="filter-form" class="filter-card">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="ค้นหากิจกรรม & คอมมูนิตี้ที่คุณชอบ" class="search-box">

        <div class="filter-row">
            <fieldset>
                <legend>ประเภทกิจกรรม</legend>
                <label>
                    <input type="radio" name="sport" value="" {{ request('sport') == '' ? 'checked' : '' }}>
                    ทั้งหมด
                </label>
                @foreach ($sports as $sport)
                    <label>
                        <input type="radio" name="sport" value="{{ $sport->id }}" {{ request('sport') == $sport->id ? 'checked' : '' }}>
                        {{ $sport->name }}
                    </label>
                @endforeach
            </fieldset>

            <fieldset>
                <legend>วันที่</legend>
                <input type="date" name="date" value="{{ request('date') }}">
            </fieldset>

            <fieldset>
                <legend>ช่วงเวลา</legend>
                <input type="time" name="from" value="{{ request('from') }}">
                ถึง
                <input type="time" name="to" value="{{ request('to') }}">
            </fieldset>

            <fieldset>
                <legend>ระดับกิจกรรม</legend>
                <label>
                    <input type="radio" name="level" value="" {{ request('level') == '' ? 'checked' : '' }}>
                    ทั้งหมด
                </label>
                @foreach ($levels as $value => $label)
                    <label>
                        <input type="radio" name="level" value="{{ $value }}" {{ request('level') == $value ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </fieldset>

            <fieldset>
                <legend>สถานที่</legend>
                <input type="text" name="location" value="{{ request('location') }}" placeholder="เช่น มหาวิทยาลัยขอนแก่น">
            </fieldset>
        </div>
    </form>

    <div id="activity-list">
        <p class="act-count">กิจกรรมที่พบ ({{ $activities->count() }})</p>

        <div class="act-grid">
            @forelse ($activities as $activity)
                <div class="act-card">
                    @if ($activity->cover_image)
                        <img src="{{ asset('storage/'.$activity->cover_image) }}" alt="{{ $activity->name }}"
                            style="object-position: {{ $activity->cover_position ?? '50% 50%' }};">
                    @endif
                    <h3>{{ $activity->name }}</h3>

                    @if ($activity->description)
                        <p>{{ \Illuminate\Support\Str::limit($activity->description, 100) }}</p>
                    @endif

                    <span class="act-tag">{{ $activity->sport->name }} · {{ $levels[$activity->skill_level] ?? '-' }}</span>

                    <p>📅 {{ $activity->date->format('d/m/Y') }} {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}</p>
                    <p>📍 {{ $activity->location->name }}</p>

                    @if ($activity->community)
                        <p>👥 {{ $activity->community->name }}</p>
                    @endif

                    <p>🙋 เข้าร่วมแล้ว: {{ $activity->registered_count }}/{{ $activity->max_participants }}</p>

                    <a href="{{ route('activities.show', $activity) }}" class="btn outline">ดูรายละเอียด</a>
                </div>
            @empty
                <p class="empty">ไม่พบกิจกรรม</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const filterForm = document.getElementById('filter-form');
    const activityList = document.getElementById('activity-list');

    async function loadActivities() {
        // เอาเฉพาะช่องที่มีค่า จะได้ URL สะอาด เช่น /activities?sport=1&date=2026-10-01
        const params = new URLSearchParams();
        new FormData(filterForm).forEach((value, key) => {
            if (value !== '') {
                params.append(key, value);
            }
        });

        const query = params.toString();
        const url = filterForm.action + (query ? '?' + query : '');

        const response = await fetch(url);
        const html = await response.text();

        // ดึงหน้า /activities ทั้งหน้ามา แล้วสลับเฉพาะส่วนรายการ
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const newList = doc.getElementById('activity-list');

        if (!response.ok || !newList) {
            // เช่น session หมดอายุแล้วโดนพาไปหน้า login ให้โหลดหน้าปกติแทน
            window.location.href = url;
            return;
        }

        activityList.innerHTML = newList.innerHTML;
        history.replaceState(null, '', url);
    }

    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();
        loadActivities();
    });

    let typingTimer;

    filterForm.querySelectorAll('input').forEach((field) => {
        if (field.type === 'text') {
            // ช่องพิมพ์ รอให้หยุดพิมพ์ครู่หนึ่งก่อนค่อยค้นหา
            field.addEventListener('input', () => {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(loadActivities, 300);
            });
        } else {
            field.addEventListener('change', loadActivities);
        }
    });
</script>
@endsection