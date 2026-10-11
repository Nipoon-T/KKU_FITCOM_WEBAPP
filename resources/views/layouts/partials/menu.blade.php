@php
    // ===== รายการเมนู: แต่ละคนแก้แค่บรรทัดของตัวเอง =====
    // auth => ต้อง login ถึงเห็น, admin => เห็นเฉพาะ admin
    $menuLinks = [
        ['route' => 'home', 'label' => 'หน้าแรก', 'icon' => '🏠'],
        ['route' => 'activities.index', 'label' => 'ค้นหากิจกรรม', 'icon' => '🏃'],          // คนที่ 3
        ['route' => 'community.index', 'label' => 'Community', 'icon' => '👥'],            // คนที่ 2
        ['route' => 'recommendations.index', 'label' => 'แนะนำสำหรับคุณ', 'icon' => '✨', 'auth' => true], // คนที่ 4
        ['route' => 'dashboard', 'label' => 'Dashboard ของฉัน', 'icon' => '📊', 'auth' => true], // คนที่ 5
        ['route' => 'profile.setup', 'label' => 'โปรไฟล์ของฉัน', 'icon' => '👤', 'auth' => true],  // คนที่ 1
        ['route' => 'admin.dashboard', 'label' => 'จัดการระบบ', 'icon' => '🛠️', 'admin' => true], // คนที่ 5
    ];

    $isAdmin = auth()->check() && auth()->user()->role === 'admin';
@endphp

<div class="menu-overlay" id="menuOverlay"></div>

<nav class="side-menu" id="sideMenu">
    <div class="side-menu-header">
        @auth
            <div class="menu-user">👋 {{ auth()->user()->name }}</div>
        @else
            <div class="menu-user">ยินดีต้อนรับ!</div>
        @endauth
        <button class="menu-close" id="menuClose" aria-label="ปิดเมนู">✕</button>
    </div>

    @foreach ($menuLinks as $link)
        @continue(! Route::has($link['route']))
        @continue(! empty($link['auth']) && ! auth()->check())
        @continue(! empty($link['admin']) && ! $isAdmin)

        <a href="{{ route($link['route']) }}"
           class="{{ request()->routeIs($link['route']) ? 'active' : '' }}">
            <span>{{ $link['icon'] }}</span> {{ $link['label'] }}
        </a>
    @endforeach

    <div class="menu-bottom">
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">🚪 ออกจากระบบ</button>
            </form>
        @else
            <a href="{{ route('login') }}">🔑 เข้าสู่ระบบ</a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}">📝 สมัครสมาชิก</a>
            @endif
        @endauth
    </div>
</nav>