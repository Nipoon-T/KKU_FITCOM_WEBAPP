<x-layouts::app :title="'แนะนำสำหรับคุณ'">
    <div style="max-width: 700px; margin: 40px auto; padding: 20px;">
        <h1>แนะนำสำหรับคุณ</h1>

        @forelse ($recommendations as $activity)
            <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 15px;">
                <h2>{{ $activity->name }}</h2>
                <p>กีฬา: {{ $activity->sport->name ?? '-' }}</p>
                <p>สถานที่: {{ $activity->location->name ?? '-' }}</p>
                <p><strong>Match {{ $activity->match_score }}%</strong></p>
            </div>
        @empty
            <p>ยังไม่มีกิจกรรมแนะนำ ลองกรอกโปรไฟล์ให้ครบก่อนนะ</p>
        @endforelse
    </div>
</x-layouts::app>
