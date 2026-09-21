<x-layouts::app :title="'กิจกรรมของฉัน'">
    <div class="mx-auto w-full max-w-3xl space-y-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">กิจกรรมที่ฉันจัด</h1>
            <a href="{{ route('activities.create') }}" class="rounded bg-cyan-600 px-4 py-2 text-white">+ สร้างกิจกรรม</a>
        </div>

        @if (session('status'))
            <p class="text-green-600">{{ session('status') }}</p>
        @endif

        @forelse ($activities as $activity)
            <div class="space-y-2 rounded-lg border p-4">
                <h2 class="font-semibold">
                    <a href="{{ route('activities.show', $activity) }}" class="underline">{{ $activity->name }}</a>
                </h2>
                <p class="text-sm">
                    {{ $activity->sport->name }} · {{ $activity->date->format('d/m/Y') }}
                    {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}
                    · {{ $activity->location->name }}
                </p>
                <p class="text-sm">ผู้เข้าร่วม {{ $activity->participants->count() }}/{{ $activity->max_participants }} คน</p>

                <ul class="divide-y">
                    @forelse ($activity->participants as $participant)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $participant->user->name }}</span>

                            @if ($participant->attendance)
                                <span class="text-sm text-green-600">
                                    เช็คชื่อแล้ว {{ $participant->attendance->checked_in_at->format('H:i') }}
                                </span>
                            @else
                                <form method="POST" action="{{ route('activities.checkin', $activity) }}">
                                    @csrf
                                    <input type="hidden" name="participant_id" value="{{ $participant->id }}">
                                    <button type="submit" class="rounded border px-3 py-1 text-sm">เช็คชื่อ</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="py-2 text-sm">ยังไม่มีผู้ลงทะเบียน</li>
                    @endforelse
                </ul>
            </div>
        @empty
            <p>คุณยังไม่ได้สร้างกิจกรรม</p>
        @endforelse
    </div>
</x-layouts::app>