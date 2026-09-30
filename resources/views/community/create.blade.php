<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Community</title>
</head>
<body>

    <h1>Create Community</h1>

    <form action="{{ route('community.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div>
            <label for="name">Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
            >
        </div>

        <div>
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
            >{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="sport_id">Sport</label>
            <select id="sport_id" name="sport_id">
                <option value=""> ระบุประเภทกีฬา </option>

                @foreach ($sports as $sport)
                    <option
                        value="{{ $sport->id }}"
                        {{ old('sport_id') == $sport->id ? 'selected' : '' }}
                    >
                        {{ $sport->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="privacy">Privacy</label>
            <select id="privacy" name="privacy" required>
                <option value="public">Public</option>
                <option value="private">Private</option>
            </select>
        </div>

        <div>
            <label for="cover_image">Cover Image</label>
                <input
                    type="file"
                    id="cover_image"
                    name="cover_image"
                    accept="image/*"
                >
        </div>

        <button type="submit">Create Community</button>
    </form>

    <a href="{{ route('community.index') }}">
        Back to Communities
    </a>

</body>
</html>