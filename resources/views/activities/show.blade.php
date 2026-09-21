<x-layouts::app :title="$activity->name">
    <div class="mx-auto w-full max-w-2xl space-y-4 p-4">
        <a href="{{ route('activities.index') }}" class="text-sm underline">← กลับ</a>

        @if (session('status'))
            <p class="text-green-600">{{ session('status') }}</p>
        @endif

        @if ($errors->has('activity'))
            <p class="text-red-600">{{ $errors->first('activity') }}</p>
        @endif

        <h1 class="text-2xl font-semibold">{{ $activity->name }}</h1>

        <div class="space-y-1">
            <p>กีฬา: {{ $activity->sport->name }}</p>
            <p>ระดับ: {{ \App\Models\Activity::SKILL_LEVELS[$activity->skill_level] ?? '-' }}</p>
            <p>วันที่: {{ $activity->date->format('d/m/Y') }} {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}</p>
            <p>สถานที่: {{ $activity->location->name }}</p>
            <p>ผู้จัด: {{ $activity->creator->name }}</p>
            <p>ผู้เข้าร่วม: {{ $registeredCount }}/{{ $activity->max_participants }} คน</p>
        </div>

        @if ($activity->description)
            <p>{{ $activity->description }}</p>
        @endif

        @if ($myParticipation?->status === 'registered')
            <p class="font-semibold">ลงทะเบียนแล้ว</p>
        @elseif ($registeredCount >= $activity->max_participants)
            <p class="font-semibold">เต็มแล้ว</p>
        @else
            <form method="POST" action="{{ route('activities.register', $activity) }}">
                @csrf
                <button type="submit" class="rounded bg-cyan-600 px-4 py-2 text-white">เข้าร่วมกิจกรรม</button>
            </form>
        @endif
    </div>
</x-layouts::app>