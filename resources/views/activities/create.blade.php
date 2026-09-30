<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างกิจกรรม</title>
</head>
<body>

    <a href="{{ route('activities.index') }}">← กลับ</a>

    <h1>สร้างกิจกรรม</h1>

    @include('activities._form', [
        'activity' => null,
        'action' => route('activities.store'),
        'method' => 'POST',
        'submitLabel' => 'สร้างกิจกรรม',
    ])

</body>
</html>