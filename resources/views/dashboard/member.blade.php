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
</style>
@endsection

@section('content')
<div class="dash">
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