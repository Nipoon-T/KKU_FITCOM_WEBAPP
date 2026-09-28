<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Activity</title>
</head>
<body>

    <h1>Create Activity</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('activities.store') }}" method="POST">
        @csrf

        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        </div> <br>

        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description">{{ old('description') }}</textarea>
        </div> <br>

        <div>
            <label for="sport_id">Sport</label>
            <select id="sport_id" name="sport_id" required>
                <option value="">เลือกกีฬา</option>
                @foreach ($sports as $sport)
                    <option value="{{ $sport->id }}" {{ old('sport_id') == $sport->id ? 'selected' : '' }}>
                        {{ $sport->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="skill_level">Level</label>
            <select id="skill_level" name="skill_level" required>
                @foreach ($levels as $value => $label)
                    <option value="{{ $value }}" {{ old('skill_level', 1) == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="community_id">Community (ไม่บังคับ)</label>
            <select id="community_id" name="community_id">
                <option value="">ไม่สังกัดกลุ่ม</option>
                @foreach ($communities as $community)
                    <option value="{{ $community->id }}"
                        {{ old('community_id', request('community')) == $community->id ? 'selected' : '' }}>
                        {{ $community->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="location_id">Location (เลือกที่มีอยู่)</label>
            <select id="location_id" name="location_id">
                <option value="">หรือกรอกสถานที่ใหม่ด้านล่าง</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="new_location_name">New Location Name</label>
            <input type="text" id="new_location_name" name="new_location_name" value="{{ old('new_location_name') }}">
        </div>

        <div>
            <label for="new_location_address">New Location Address</label>
            <input type="text" id="new_location_address" name="new_location_address" value="{{ old('new_location_address') }}">
        </div>

        <div>
            <label for="date">Date</label>
            <input type="date" id="date" name="date" value="{{ old('date') }}" required>
        </div>

        <div>
            <label for="start_time">Start</label>
            <input type="time" id="start_time" name="start_time" value="{{ old('start_time') }}" required>
        </div>

        <div>
            <label for="end_time">End</label>
            <input type="time" id="end_time" name="end_time" value="{{ old('end_time') }}" required>
        </div>

        <div>
            <label for="max_participants">Max Participants</label>
            <input type="number" id="max_participants" name="max_participants" min="1" value="{{ old('max_participants', 10) }}" required>
        </div>

        <button type="submit">Create</button>
    </form>

    <p>
        <a href="{{ route('activities.index') }}">← Back to Activities</a>
    </p>

</body>
</html>