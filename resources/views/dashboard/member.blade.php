@extends('layouts.site')

@section('title', 'Dashboard ของฉัน - KKU FitCom')

@section('styles')
<style>
    .dash { max-width: 960px; margin: 24px auto; padding: 0 16px; }
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .card h3 { font-size: 14px; color: #777; margin-bottom: 8px; font-weight: normal; }
    .card .big { font-size: 32px; font-weight: bold; color: #70c5d3; }
    .section { margin-bottom: 24px; }
    .section h2 { font-size: 18px; margin-bottom: 12px; }
    .badges { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
    .badge { text-align: center; padding: 16px 8px; border-radius: 12px; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .badge .icon { font-size: 40px; }
    .badge .name { font-weight: bold; margin-top: 6px; font-size: 14px; }
    .badge .desc { font-size: 12px; color: #777; margin-top: 4px; }
    .badge.locked { opacity: .35; filter: grayscale(1); }
    .logs { width: 100%; border-collapse: collapse; }
    .logs td { padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 14px; }
    .plus { color: #2e9e5b; font-weight: bold; }
    .minus { color: #d9534f; font-weight: bold; }

    .act-list { list-style: none; padding: 0; margin: 0; }
    .act-list li { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 4px; border-bottom: 1px solid #eee; }
    .act-list a { color: inherit; text-decoration: none; }
    .act-name { font-weight: bold; font-size: 15px; }
    .act-meta { font-size: 13px; color: #777; margin-top: 2px; }
    .chip { font-size: 12px; padding: 3px 10px; border-radius: 10px; white-space: nowrap; }
    .chip.ok { background: #e3f4e9; color: #2e7d4f; }
    .chip.wait { background: #e3f4f7; color: #3a9fb0; }
    .chip.miss { background: #f1f1f1; color: #999; }

    .empty-state { text-align: center; padding: 20px 0 8px; color: #777; }
    .btn-find { display: inline-block; margin-top: 12px; padding: 10px 22px; border-radius: 10px; background: #70c5d3; color: #fff; text-decoration: none; font-weight: bold; transition: background .2s, transform .1s; }
    .btn-find:hover { background: #5ab3c2; }
    .btn-find:active { transform: scale(.97); }

    .hello { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
    .avatar { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .avatar-fallback { display: flex; align-items: center; justify-content: center; background: #70c5d3; color: #fff; font-size: 28px; font-weight: bold; }
    .hello h1 { font-size: 22px; margin: 0; }
    .hello .sub { font-size: 14px; color: #777; margin-top: 4px; }
    .hello .tags span { display: inline-block; font-size: 12px; padding: 2px 10px; border-radius: 10px; background: #e3f4f7; color: #3a9fb0; margin: 6px 6px 0 0; }
    .hello .edit { margin-left: auto; font-size: 14px; color: #3a9fb0; text-decoration: none; white-space: nowrap; }
</style>
@endsection

@section('content')
<div class="dash">

    <div class="hello card">
        @if ($profile?->avatar)
            <img class="avatar" src="{{ asset('storage/'.$profile->avatar) }}" alt="{{ $user->name }}">
        @else
            <div class="avatar avatar-fallback">{{ mb_substr($user->name, 0, 1) }}</div>
        @endif

        <div>
            <h1>สวัสดี, {{ $user->name }} 👋</h1>
            <div class="sub">{{ $profile?->bio ?: 'พร้อมออกกำลังกายวันนี้หรือยัง?' }}</div>
            <div class="tags">
                @if ($profile?->goal) <span>🎯 {{ $profile->goal }}</span> @endif
                @if ($profile?->skill_level) <span>📈 ระดับ {{ $profile->skill_level }}</span> @endif
            </div>
        </div>

        @if (Route::has('profile.setup'))
            <a class="edit" href="{{ route('profile.setup') }}">✏️ แก้ไขโปรไฟล์</a>
        @endif
    </div>

    <div class="stats">
        <div class="card">
            <h3>แต้มสะสม</h3>
            <div class="big">{{ number_format($totalPoints) }}</div>
        </div>
        <div class="card">
            <h3>Streak ปัจจุบัน</h3>
            <div class="big">🔥 {{ $currentStreak }} วัน</div>
        </div>
        <div class="card">
            <h3>สถิติสูงสุด</h3>
            <div class="big">{{ $longestStreak }} วัน</div>
        </div>
        <div class="card">
            <h3>เหรียญที่ได้</h3>
            <div class="big">{{ count($earnedIds) }} / {{ $badges->count() }}</div>
        </div>
    </div>

    <div class="section card">
        <h2>แต้มย้อนหลัง 14 วัน</h2>
        <canvas id="pointsChart" height="100"></canvas>
    </div>

    <div class="section">
        <h2>เหรียญของฉัน</h2>
        <div class="badges">
            @foreach ($badges as $badge)
                <div class="badge {{ in_array($badge->id, $earnedIds) ? '' : 'locked' }}">
                    <div class="icon">{{ $badge->icon }}</div>
                    <div class="name">{{ $badge->name }}</div>
                    <div class="desc">{{ $badge->description }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="section card">
        <h2>ประวัติแต้มล่าสุด</h2>
        @if ($recentLogs->isEmpty())
            <p>ยังไม่มีประวัติแต้ม ไปเข้าร่วมกิจกรรมกันเถอะ!</p>
        @else
            <table class="logs">
                @foreach ($recentLogs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $log->reason }}{{ $log->activity ? ' — '.$log->activity->name : '' }}</td>
                        <td class="{{ $log->points_change >= 0 ? 'plus' : 'minus' }}">
                            {{ $log->points_change >= 0 ? '+' : '' }}{{ $log->points_change }}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>

    <div class="section card">
        <h2>📅 กิจกรรมที่กำลังจะมาถึง</h2>
        @forelse ($upcoming as $p)
            @if ($loop->first) <ul class="act-list"> @endif
            <li>
                <div>
                    <div class="act-name">
                        @if (Route::has('activities.show'))
                            <a href="{{ route('activities.show', $p->activity) }}">{{ $p->activity->name }}</a>
                        @else
                            {{ $p->activity->name }}
                        @endif
                    </div>
                    <div class="act-meta">
                        {{ $p->activity->date->format('d/m/Y') }} · {{ substr($p->activity->start_time, 0, 5) }} น.
                        · {{ $p->activity->location?->name }} · {{ $p->activity->sport?->name }}
                    </div>
                </div>
                @if ($p->attendance)
                    <span class="chip ok">✅ เช็คชื่อแล้ว</span>
                @else
                    <span class="chip wait">ลงทะเบียนแล้ว</span>
                @endif
            </li>
            @if ($loop->last) </ul> @endif
        @empty
            <div class="empty-state">
                <div>ยังไม่มีกิจกรรมที่สมัครไว้</div>
                @if (Route::has('activities.index'))
                    <a class="btn-find" href="{{ route('activities.index') }}">🔍 ไปหากิจกรรมกัน!</a>
                @endif
            </div>
        @endforelse
    </div>

    <div class="section card">
        <h2>🏃 ประวัติกิจกรรมที่เข้าร่วม</h2>
        @forelse ($history as $p)
            @if ($loop->first) <ul class="act-list"> @endif
            <li>
                <div>
                    <div class="act-name">{{ $p->activity->name }}</div>
                    <div class="act-meta">
                        {{ $p->activity->date->format('d/m/Y') }} · {{ $p->activity->location?->name }} · {{ $p->activity->sport?->name }}
                    </div>
                </div>
                @if ($p->attendance)
                    <span class="chip ok">✅ เข้าร่วมแล้ว +{{ \App\Services\GamificationService::POINTS_PER_ACTIVITY }}</span>
                @else
                    <span class="chip miss">ไม่ได้เช็คชื่อ</span>
                @endif
            </li>
            @if ($loop->last) </ul> @endif
        @empty
            <p>ยังไม่มีประวัติกิจกรรม</p>
        @endforelse
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('pointsChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'แต้มที่ได้',
                data: @json($chartData),
                backgroundColor: '#70c5d3',
                borderRadius: 6,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
</script>
@endsection