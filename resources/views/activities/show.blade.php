<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $activity->name }}</title>
</head>
<body>

    <a href="{{ route('activities.index') }}">
        ← Back to Activities
    </a>

    <h1>{{ $activity->name }}</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p>{{ $errors->first() }}</p>
    @endif

    <p>
        <strong>Description:</strong>
        {{ $activity->description ?: 'No description.' }}
    </p>

    <p>
        <strong>Sport:</strong>
        {{ $activity->sport->name }}
    </p>

    <p>
        <strong>Level:</strong>
        {{ $levels[$activity->skill_level] ?? '-' }}
    </p>

    <p>
        <strong>Date:</strong>
        {{ $activity->date->format('d/m/Y') }}
        {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
    </p>

    <p>
        <strong>Location:</strong>
        {{ $activity->location->name }}
    </p>

    @if ($activity->community)
        <p>
            <strong>Community:</strong>
            <a href="{{ route('community.show', $activity->community) }}">
                {{ $activity->community->name }}
            </a>
        </p>
    @endif

    <p>
        <strong>Created by:</strong>
        {{ $activity->creator->name }}
    </p>

    <p>
        <strong>Participants:</strong>
        {{ $registeredCount }}/{{ $activity->max_participants }}
    </p>

    <hr>

    @if ($myParticipation?->status === 'registered')
        <p>ลงทะเบียนแล้ว</p>
    @elseif ($activity->date->lt(today()))
        <p>กิจกรรมนี้จบไปแล้ว</p>
    @elseif ($registeredCount >= $activity->max_participants)
        <p>กิจกรรมเต็มแล้ว</p>
    @else
        <form action="{{ route('activities.register', $activity) }}" method="POST">
            @csrf
            <button type="submit">เข้าร่วมกิจกรรม</button>
        </form>
    @endif

    @if ((int) $activity->created_by === (int) auth()->id())
        <p>
            <a href="{{ route('activities.mine') }}">จัดการผู้เข้าร่วมและเช็คชื่อ</a>
        </p>
    @endif

</body>
</html>