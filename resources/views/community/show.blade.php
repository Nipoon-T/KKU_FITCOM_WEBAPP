<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $community->name }}</title>
</head>
<body>

    <a href="{{ route('community.index') }}">
        ← Back to Communities
    </a>

    <h1>{{ $community->name }}</h1>

    @if ($community->cover_image)
        <img
            src="{{ asset('storage/' . $community->cover_image) }}"
            alt="{{ $community->name }}"
            width="400"
        >
    @endif

    <p>
        <strong>Description:</strong>
        {{ $community->description ?: 'No description.' }}
    </p>

    <p>
        <strong>Sport:</strong>
        {{ $community->sport?->name ?? 'Not specified' }}
    </p>

    <p>
        <strong>Privacy:</strong>
        {{ $community->privacy }}
    </p>

    <p>
        <strong>Created by:</strong>
        {{ $community->creator->name }}
    </p>

    <hr>

    <h2>Activities</h2>

    @forelse ($community->activities as $activity)
        <div>
            <strong>{{ $activity->name }}</strong>
            <p>{{ $activity->description }}</p>
            <p>Date: {{ $activity->date }}</p>
        </div>
    @empty
        <p>No activities yet.</p>
    @endforelse

    <hr>

    <h2>Members</h2>

    @forelse ($community->members as $member)
        <div>
            {{ $member->user->name }}
            — {{ $member->role }}
        </div>
    @empty
        <p>No members yet.</p>
    @endforelse

    <hr>

    <h2>Posts</h2>

    @forelse ($community->posts as $post)
        <div>
            <strong>{{ $post->user->name }}</strong>
            <p>{{ $post->content }}</p>
        </div>
    @empty
        <p>No posts yet.</p>
    @endforelse

</body>
</html>