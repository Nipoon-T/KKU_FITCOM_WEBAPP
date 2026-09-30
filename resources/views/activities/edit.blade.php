<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขกิจกรรม - {{ $activity->name }}</title>
</head>
<body>

    <a href="{{ route('activities.mine') }}">← กลับ</a>

    <h1>แก้ไขกิจกรรม</h1>

    @include('activities._form', [
        'activity' => $activity,
        'action' => route('activities.update', $activity),
        'method' => 'PUT',
        'submitLabel' => 'บันทึกการแก้ไข',
    ])

</body>
</html>
