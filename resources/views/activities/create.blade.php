<x-layouts::app :title="'สร้างกิจกรรม'">
    <div class="mx-auto w-full max-w-2xl space-y-4 p-4">
        <h1 class="text-2xl font-semibold">สร้างกิจกรรม</h1>

        <form method="POST" action="{{ route('activities.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm">ชื่อกิจกรรม</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm">รายละเอียด</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full rounded border px-3 py-2 dark:bg-zinc-800">{{ old('description') }}</textarea>
                @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="sport_id" class="block text-sm">กีฬา</label>
                    <select name="sport_id" id="sport_id" class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                        <option value="">เลือกกีฬา</option>
                        @foreach ($sports as $sport)
                            <option value="{{ $sport->id }}" @selected((int) old('sport_id') === $sport->id)>{{ $sport->name }}</option>
                        @endforeach
                    </select>
                    @error('sport_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="skill_level" class="block text-sm">ระดับ</label>
                    <select name="skill_level" id="skill_level" class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                        @foreach (\App\Models\Activity::SKILL_LEVELS as $value => $label)
                            <option value="{{ $value }}" @selected((int) old('skill_level', 1) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('skill_level') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="location_id" class="block text-sm">สถานที่ (เลือกที่มีอยู่)</label>
                <select name="location_id" id="location_id" class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                    <option value="">-- หรือกรอกสถานที่ใหม่ด้านล่าง --</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((int) old('location_id') === $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
                @error('location_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="new_location_name" class="block text-sm">สถานที่ใหม่ (ชื่อ)</label>
                    <input type="text" name="new_location_name" id="new_location_name" value="{{ old('new_location_name') }}"
                        class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                    @error('new_location_name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new_location_address" class="block text-sm">สถานที่ใหม่ (ที่อยู่)</label>
                    <input type="text" name="new_location_address" id="new_location_address" value="{{ old('new_location_address') }}"
                        class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                    @error('new_location_address') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="date" class="block text-sm">วันที่</label>
                    <input type="date" name="date" id="date" value="{{ old('date') }}"
                        class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                    @error('date') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="start_time" class="block text-sm">เริ่ม</label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}"
                        class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                    @error('start_time') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="end_time" class="block text-sm">สิ้นสุด</label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}"
                        class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                    @error('end_time') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="max_participants" class="block text-sm">จำนวนผู้เข้าร่วมสูงสุด</label>
                <input type="number" name="max_participants" id="max_participants" min="1"
                    value="{{ old('max_participants', 10) }}"
                    class="w-full rounded border px-3 py-2 dark:bg-zinc-800">
                @error('max_participants') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="rounded bg-cyan-600 px-4 py-2 text-white">สร้างกิจกรรม</button>
        </form>
    </div>
</x-layouts::app>