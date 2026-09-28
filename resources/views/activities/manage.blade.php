<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Activities</title>
</head>
<body>

    <h1>My Activities</h1>

    <a href="{{ route('activities.index') }}">← Back to Activities</a>
    |
    <a href="{{ route('activities.create') }}">Create Activity</a>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <hr>

    @forelse ($activities as $activity)
        <div>
            <h2>
                <a href="{{ route('activities.show', $activity) }}">
                    {{ $activity->name }}
                </a>
            </h2>

            <p>
                {{ $activity->sport->name }}
                | {{ $activity->date->format('d/m/Y') }}
                {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
                | {{ $activity->location->name }}
            </p>

            <p>Participants: {{ $activity->participants->count() }}/{{ $activity->max_participants }}</p>

            @forelse ($activity->participants as $participant)
                <div>
                    {{ $participant->user->name }}

                    @if ($participant->attendance)
                        — เช็คชื่อแล้ว {{ $participant->attendance->checked_in_at->timezone('Asia/Bangkok')->format('H:i') }}
                    @else
                        <form action="{{ route('activities.checkin', $activity) }}" method="POST" style="display:inline">
                            @csrf
                            <input type="hidden" name="participant_id" value="{{ $participant->id }}">
                            <button type="submit">เช็คชื่อ</button>
                        </form>
                    @endif
                </div>
            @empty
                <p>ยังไม่มีผู้ลงทะเบียน</p>
            @endforelse
        </div>
        <hr>
    @empty
        <p>คุณยังไม่ได้สร้างกิจกรรม</p>
    @endforelse

</body>
</html>