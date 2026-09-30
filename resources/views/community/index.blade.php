<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communities</title>
</head>
<body>

    <h1>Communities</h1>

    <a href="{{ route('community.create') }}">
        Create Community
    </a>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @forelse ($communities as $community)
        <div>
            <h2>
                <a href="{{ route('community.show', $community) }}">
                    {{ $community->name }}
                </a>
            </h2>

            <p>{{ $community->description }}</p>

            <p>
                Sport: {{ $community->sport?->name ?? 'Not specified' }}
            </p>

            <p>
                Privacy: {{ $community->privacy }}
            </p>
        </div>
        <hr>
    @empty
        <p>No communities yet.</p>
    @endforelse

</body>
</html>