<x-layouts::app :title="'กิจกรรม'">
    <div class="mx-auto w-full max-w-5xl space-y-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">ค้นหากิจกรรม</h1>
            <div class="flex gap-2">
                <a href="{{ route('activities.mine') }}" class="rounded border px-4 py-2">กิจกรรมของฉัน</a>
                <a href="{{ route('activities.create') }}" class="rounded bg-cyan-600 px-4 py-2 text-white">+ สร้างกิจกรรม</a>
            </div>
        </div>

        <form method="GET" action="{{ route('activities.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="sport" class="block text-sm">กีฬา</label>
                <select name="sport" id="sport" class="rounded border px-3 py-2 dark:bg-zinc-800">
                    <option value="">ทั้งหมด</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" @selected((int) request('sport') === $sport->id)>
                            {{ $sport->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date" class="block text-sm">วันที่</label>
                <input type="date" name="date" id="date" value="{{ request('date') }}"
                    class="rounded border px-3 py-2 dark:bg-zinc-800">
            </div>

            <div>
                <label for="level" class="block text-sm">ระดับ</label>
                <select name="level" id="level" class="rounded border px-3 py-2 dark:bg-zinc-800">
                    <option value="">ทั้งหมด</option>
                    @foreach (\App\Models\Activity::SKILL_LEVELS as $value => $label)
                        <option value="{{ $value }}" @selected((int) request('level') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="rounded bg-cyan-600 px-4 py-2 text-white">ค้นหา</button>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($activities as $activity)
                <a href="{{ route('activities.show', $activity) }}"
                    class="block space-y-1 rounded-lg border p-4 hover:shadow">
                    <h2 class="font-semibold">{{ $activity->name }}</h2>
                    <p class="text-sm">{{ $activity->sport->name }} · {{ \App\Models\Activity::SKILL_LEVELS[$activity->skill_level] ?? '-' }}</p>
                    <p class="text-sm">{{ $activity->date->format('d/m/Y') }} {{ substr($activity->start_time, 0, 5) }}-{{ substr($activity->end_time, 0, 5) }}</p>
                    <p class="text-sm">{{ $activity->location->name }}</p>
                    <p class="text-sm">{{ $activity->registered_count }}/{{ $activity->max_participants }} คน</p>
                </a>
            @empty
                <p class="col-span-full">ไม่พบกิจกรรม</p>
            @endforelse
        </div>

        {{ $activities->links() }}
    </div>
</x-layouts::app>