@extends('layouts.site')

@section('title', 'Admin Dashboard - KKU FitCom')

@section('styles')
<style>
    .dash { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
    .dash h1 { font-size: 22px; margin-bottom: 16px; }
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .card h3 { font-size: 14px; color: #777; margin-bottom: 8px; font-weight: normal; }
    .card .big { font-size: 28px; font-weight: bold; color: #70c5d3; }
    .row { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px; }
    @media (max-width: 768px) { .row { grid-template-columns: 1fr; } }
    .card h2 { font-size: 18px; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 14px; text-align: left; }
    th { color: #777; font-weight: normal; }
    .rank { font-weight: bold; color: #70c5d3; }
    .tag { padding: 2px 10px; border-radius: 10px; font-size: 12px; background: #eee; }
    .tag.admin { background: #70c5d3; color: #fff; }
    .search { display: flex; gap: 8px; margin-bottom: 12px; }
    .search input { flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; }
    .search button, .pager a { padding: 8px 16px; border: none; border-radius: 8px; background: #70c5d3; color: #fff; text-decoration: none; cursor: pointer; }
    .pager { display: flex; justify-content: space-between; align-items: center; margin-top: 12px; font-size: 14px; }
</style>
@endsection

@section('content')
<div class="dash">
    <h1>ภาพรวมระบบ (Admin)</h1>

    <div class="stats">
        <div class="card"><h3>สมาชิกทั้งหมด</h3><div class="big">{{ number_format($stats['users']) }}</div></div>
        <div class="card"><h3>กิจกรรมทั้งหมด</h3><div class="big">{{ number_format($stats['activities']) }}</div></div>
        <div class="card"><h3>การสมัครเข้าร่วม</h3><div class="big">{{ number_format($stats['registrations']) }}</div></div>
        <div class="card"><h3>เช็คชื่อแล้ว</h3><div class="big">{{ number_format($stats['checkins']) }}</div></div>
        <div class="card"><h3>แต้มที่แจกไปทั้งหมด</h3><div class="big">{{ number_format($stats['points']) }}</div></div>
    </div>

    <div class="row">
        <div class="card">
            <h2>14 วันล่าสุด</h2>
            <canvas id="adminChart" height="120"></canvas>
        </div>
        <div class="card">
            <h2>🏆 แต้มสูงสุด</h2>
            @forelse ($leaderboard as $i => $row)
                <table>
                    <tr>
                        <td class="rank">#{{ $i + 1 }}</td>
                        <td>{{ $row->user?->name ?? '(ผู้ใช้ถูกลบ)' }}</td>
                        <td>{{ number_format($row->total) }} แต้ม</td>
                    </tr>
                </table>
            @empty
                <p>ยังไม่มีใครได้แต้ม</p>
            @endforelse
        </div>

    </div>
        <div id="users-table">
        @include('admin.partials.users-table')
    </div>

    <div class="card">
        <h2>จัดการผู้ใช้</h2>
        <form class="search" method="GET" action="{{ route('admin.dashboard') }}">
            <input type="text" name="q" value="{{ $search }}" placeholder="ค้นหาชื่อหรืออีเมล">
            <button type="submit">ค้นหา</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('adminChart'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                { label: 'เช็คชื่อ', data: @json($chartCheckins), borderColor: '#70c5d3', backgroundColor: '#70c5d3', tension: .3 },
                { label: 'สมาชิกใหม่', data: @json($chartUsers), borderColor: '#f0a04b', backgroundColor: '#f0a04b', tension: .3 },
            ]
        },
        options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
</script>

<script>
    const searchForm = document.querySelector('.search');
    const searchInput = searchForm.querySelector('input[name="q"]');
    const usersBox = document.getElementById('users-table');
    let searchTimer;

    // ดึงเฉพาะตารางผู้ใช้มาแทนที่ ไม่ reload ทั้งหน้า
    async function loadUsers(url) {
        usersBox.style.opacity = 0.5;
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            usersBox.innerHTML = await res.text();
            history.replaceState(null, '', url); // อัปเดต URL ให้ refresh แล้วยังเจอผลเดิม
        } finally {
            usersBox.style.opacity = 1;
        }
    }

    // กดปุ่มค้นหา / Enter
    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        loadUsers(searchForm.action + '?q=' + encodeURIComponent(searchInput.value));
    });

    // พิมพ์แล้วค้นหาเอง (รอหยุดพิมพ์ 0.3 วินาที จะได้ไม่ยิงทุกตัวอักษร)
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => searchForm.requestSubmit(), 300);
    });

    // ปุ่มก่อนหน้า/ถัดไป
    usersBox.addEventListener('click', (e) => {
        const link = e.target.closest('.pager a');
        if (link) {
            e.preventDefault();
            loadUsers(link.href);
        }
    });
</script>
@endsection