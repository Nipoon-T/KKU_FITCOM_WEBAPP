<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เช็คชื่อ - {{ $activity->name }}</title>
</head>
<body>

    <a href="{{ route('activities.mine') }}">← Back to My Activities</a>

    <h1>เช็คชื่อ: {{ $activity->name }}</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p>{{ $errors->first() }}</p>
    @endif

    <p>
        {{ $activity->sport->name }}
        | {{ $activity->date->format('d/m/Y') }}
        {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
        | {{ $activity->location->name }}
    </p>

    @unless ($canCheckin)
        <p>เช็คชื่อได้เฉพาะวันที่จัดกิจกรรม ({{ $activity->date->format('d/m/Y') }})</p>
    @endunless

    <p>
        เช็คชื่อแล้ว
        <span id="checked-count">{{ $participants->filter(fn ($p) => $p->attendance)->count() }}</span>/{{ $participants->count() }}
        คน
    </p>

    <hr>

    @forelse ($participants as $participant)
        <div>
            {{ $participant->user->name }}

            @if ($participant->attendance)
                — เช็คชื่อแล้ว {{ $participant->attendance->checked_in_at->timezone('Asia/Bangkok')->format('H:i') }}
            @elseif ($canCheckin)
                <form action="{{ route('activities.checkin', $activity) }}" method="POST" class="checkin-form" style="display:inline">
                    @csrf
                    <input type="hidden" name="participant_id" value="{{ $participant->id }}">
                    <button type="submit">เช็คชื่อ</button>
                </form>
            @else
                — ยังไม่ได้เช็คชื่อ
            @endif
        </div>
    @empty
        <p>ยังไม่มีผู้ลงทะเบียน</p>
    @endforelse

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
                form.replaceWith(document.createTextNode('— เช็คชื่อแล้ว ' + data.time));

                const counter = document.getElementById('checked-count');
                counter.textContent = Number(counter.textContent) + 1;
            });
        });
    </script>

</body>
</html>