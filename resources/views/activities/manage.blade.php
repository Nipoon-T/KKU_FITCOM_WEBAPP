<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการกิจกรรมของคุณ</title>
</head>
<body>

    <a href="{{ route('activities.index') }}">← กลับ</a>

    <h1>จัดการกิจกรรมของคุณ</h1>

    <a href="{{ route('activities.create') }}">+ สร้างกิจกรรม</a>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p>{{ $errors->first() }}</p>
    @endif

    <hr>

    <h2>แก้ไข/ยกเลิกกิจกรรม</h2>

    @forelse ($activities as $activity)
        <div>
            {{ $activity->name }}

            @unless ($activity->date->lt(today()))
                <a href="{{ route('activities.edit', $activity) }}"
                    style="display:inline-block; padding:2px 8px; border:1px solid; text-decoration:none;">
                    แก้ไข
                </a>
            @endunless

            <form action="{{ route('activities.destroy', $activity) }}" method="POST" class="delete-form" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit">ลบ</button>
            </form>
        </div>
    @empty
        <p>คุณยังไม่ได้สร้างกิจกรรม</p>
    @endforelse

    <hr>

    <h2>เช็คชื่อผู้เข้าร่วม</h2>

    @forelse ($activities as $activity)
        <div>
            <h3>{{ $activity->name }}</h3>

            <p>
                {{ $activity->date->format('d/m/Y') }}
                {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
                | {{ $activity->location->name }}
            </p>

            <progress value="{{ $activity->registered_count }}" max="{{ $activity->max_participants }}"></progress>
            {{ $activity->registered_count }}/{{ $activity->max_participants }} Registered
            | เช็คชื่อแล้ว {{ $activity->checked_count }}/{{ $activity->registered_count }}

            <p>
                <a href="{{ route('activities.attendance', $activity) }}">Check-in List</a>
            </p>
        </div>
        <hr>
    @empty
        <p>ยังไม่มีกิจกรรมให้เช็คชื่อ</p>
    @endforelse

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

</body>
</html>