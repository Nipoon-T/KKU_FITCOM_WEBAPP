<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKU FitCom - Activities</title>
</head>
<body>

    <h1>KKU FitCom</h1>

    <a href="{{ route('activities.create') }}">สร้างกิจกรรม</a>
    |
    <a href="{{ route('activities.mine') }}">จัดการกิจกรรมของฉัน</a>

    <form method="GET" action="{{ route('activities.index') }}" id="filter-form">

        <div>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="ค้นหากิจกรรม & คอมมูนิตี้ที่คุณชอบ" size="40">
        </div>

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
            <input type="text" name="location" value="{{ request('location') }}" placeholder="เช่น มหาวิทยาลัยขอนแก่น" size="40">
        </fieldset>

        <button type="submit">ค้นหา</button>
    </form>

    <hr>

    <div id="activity-list">
        <h2>กิจกรรมที่พบ ({{ $activities->count() }})</h2>

        @forelse ($activities as $activity)
            <div>
                <h3>{{ $activity->name }}</h3>

                @if ($activity->description)
                    <p>{{ \Illuminate\Support\Str::limit($activity->description, 100) }}</p>
                @endif

                <p>
                    ประเภท: {{ $activity->sport->name }}
                    | ระดับ: {{ $levels[$activity->skill_level] ?? '-' }}
                </p>

                <p>
                    วันที่: {{ $activity->date->format('d/m/Y') }}
                    {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
                </p>

                <p>สถานที่: {{ $activity->location->name }}</p>

                @if ($activity->community)
                    <p>กลุ่ม: {{ $activity->community->name }}</p>
                @endif

                <p>เข้าร่วมแล้ว: {{ $activity->registered_count }}/{{ $activity->max_participants }}</p>

                <a href="{{ route('activities.show', $activity) }}">ดูรายละเอียด</a>
            </div>
            <hr>
        @empty
            <p>ไม่พบกิจกรรม</p>
        @endforelse
    </div>

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

</body>
</html>