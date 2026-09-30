<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $activity->name }}</title>
</head>
<body>

    <a href="{{ route('activities.index') }}">← กลับ</a>

    <h1>รายละเอียดกิจกรรม</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p>{{ $errors->first() }}</p>
    @endif

    <h2>{{ $activity->name }}</h2>

    <p>{{ $activity->description ?: 'ไม่มีคำอธิบาย' }}</p>

    <p>
        <strong>วันจัดกิจกรรม:</strong>
        {{ $activity->date->format('d/m/Y') }}
        {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
    </p>

    <p>
        <strong>สถานที่:</strong>
        {{ $activity->location->name }}
        @if ($activity->location->address)
            ({{ $activity->location->address }})
        @endif
    </p>

    <p>
        <strong>ประเภทกิจกรรม:</strong>
        {{ $activity->sport->name }}
    </p>

    <p>
        <strong>ระดับกิจกรรม:</strong>
        {{ $levels[$activity->skill_level] ?? '-' }}
    </p>

    @if ($activity->community)
        <p>
            <strong>กลุ่ม:</strong>
            <a href="{{ route('community.show', $activity->community) }}">
                {{ $activity->community->name }}
            </a>
        </p>
    @endif

    <p>
        <strong>ผู้จัด:</strong>
        {{ $activity->creator->name }}
    </p>

    <p>
        <strong>ผู้เข้าร่วม:</strong>
        <span id="participant-count">{{ $registeredCount }}</span>/{{ $activity->max_participants }}
    </p>

    <hr>

    @if ($myParticipation?->status === 'registered')
        <p>เข้าร่วมแล้ว</p>
    @elseif ($activity->date->lt(today()))
        <p>กิจกรรมนี้จบไปแล้ว</p>
    @elseif ($registeredCount >= $activity->max_participants)
        <p>กิจกรรมเต็มแล้ว</p>
    @else
        <form action="{{ route('activities.register', $activity) }}" method="POST" id="register-form">
            @csrf
            <button type="submit">เข้าร่วมกิจกรรม</button>
        </form>
        <p id="register-message"></p>
    @endif

    @if ((int) $activity->created_by === (int) auth()->id())
        <hr>
        <p>สำหรับผู้จัดกิจกรรม</p>
        <p>
            @unless ($activity->date->lt(today()))
                <a href="{{ route('activities.edit', $activity) }}">แก้ไขกิจกรรม</a>
                |
            @endunless
            <a href="{{ route('activities.attendance', $activity) }}">เช็คชื่อผู้เข้าร่วม</a>
        </p>
    @endif

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
                    message.textContent = '';

                    if (data.count !== null) {
                        document.getElementById('participant-count').textContent = data.count;
                    }

                    form.replaceWith(Object.assign(document.createElement('p'), {
                        textContent: 'เข้าร่วมแล้ว',
                    }));
                } else {
                    message.textContent = data.message;
                }
            });
        }
    </script>

</body>
</html>