<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities</title>
</head>
<body>

    <h1>Activities</h1>

    <a href="{{ route('activities.create') }}">Create Activity</a>
    |
    <a href="{{ route('activities.mine') }}">My Activities</a>

    <form method="GET" action="{{ route('activities.index') }}" id="filter-form">
        <select name="sport">
            <option value="">ทุกกีฬา</option>
            @foreach ($sports as $sport)
                <option value="{{ $sport->id }}" {{ request('sport') == $sport->id ? 'selected' : '' }}>
                    {{ $sport->name }}
                </option>
            @endforeach
        </select>

        <input type="date" name="date" value="{{ request('date') }}">

        <select name="level">
            <option value="">ทุกระดับ</option>
            @foreach ($levels as $value => $label)
                <option value="{{ $value }}" {{ request('level') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <button type="submit">ค้นหา</button>
    </form>

    <hr>

    <div id="activity-list">
        @forelse ($activities as $activity)
            <div>
                <h2>
                    <a href="{{ route('activities.show', $activity) }}">
                        {{ $activity->name }}
                    </a>
                </h2>

                <p>
                    Sport: {{ $activity->sport->name }}
                    | Level: {{ $levels[$activity->skill_level] ?? '-' }}
                </p>

                <p>
                    Date: {{ $activity->date->format('d/m/Y') }}
                    {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
                </p>

                <p>Location: {{ $activity->location->name }}</p>

                @if ($activity->community)
                    <p>Community: {{ $activity->community->name }}</p>
                @endif

                <p>Participants: {{ $activity->registered_count }}/{{ $activity->max_participants }}</p>
            </div>
            <hr>
        @empty
            <p>No activities found.</p>
        @endforelse
    </div>

    <script>
        const filterForm = document.getElementById('filter-form');
        const activityList = document.getElementById('activity-list');

        async function loadActivities() {
            // เอาเฉพาะช่องที่มีค่า จะได้ URL สะอาด เช่น /activities?sport=1
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

        filterForm.querySelectorAll('select, input').forEach((field) => {
            field.addEventListener('change', loadActivities);
        });
    </script>

</body>
</html>