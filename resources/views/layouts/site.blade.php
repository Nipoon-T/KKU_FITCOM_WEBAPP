<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'KKU FitCom')</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
        }

        /* ===== HEADER ===== */
        .header {
            height: 64px;
            background: #70c5d3;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 20px;

            color: white;
        }

        /* Hamburger */
        .menu-btn {
            width: 32px;
            height: 32px;

            border: none;
            background: transparent;

            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 5px;

            cursor: pointer;
        }

        .menu-btn span {
            display: block;
            width: 23px;
            height: 2px;
            background: white;
            border-radius: 2px;
        }

        /* Logo / Name */
        .logo {
            font-size: 20px;
            font-weight: bold;
            color: white;
        }

        /* Search */
        .search-btn {
            width: 32px;
            height: 32px;

            border: none;
            background: transparent;

            cursor: pointer;

            position: relative;
        }

        .search-icon {
            width: 18px;
            height: 18px;

            border: 3px solid white;
            border-radius: 50%;

            position: absolute;
            top: 4px;
            left: 3px;
        }

        .search-icon::after {
            content: "";

            width: 9px;
            height: 3px;

            background: white;
            border-radius: 2px;

            position: absolute;
            right: -7px;
            bottom: -4px;

            transform: rotate(45deg);
        }

        /* ===== CONTENT ===== */
        .content {
            padding: 20px;
        }

        .menu-btn, .search-btn { cursor: pointer; }
        .menu-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.4); opacity: 0; pointer-events: none; transition: opacity .25s; z-index: 90; }
        .menu-overlay.open { opacity: 1; pointer-events: auto; }
        .side-menu { position: fixed; top: 0; left: 0; bottom: 0; width: 270px; max-width: 80vw; background: #fff; transform: translateX(-100%); transition: transform .25s; z-index: 100; display: flex; flex-direction: column; box-shadow: 2px 0 12px rgba(0,0,0,.15); }
        .side-menu.open { transform: translateX(0); }
        .side-menu-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #70c5d3; color: #fff; font-weight: bold; }
        .menu-close { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; }
        .side-menu a, .side-menu .menu-bottom button { display: flex; gap: 12px; align-items: center; padding: 14px 20px; color: #333; text-decoration: none; font-size: 15px; background: none; border: none; width: 100%; text-align: left; cursor: pointer; font-family: inherit; }
        .side-menu a:hover, .side-menu .menu-bottom button:hover { background: #f0f9fb; }
        .side-menu a.active { background: #e3f4f7; color: #3a9fb0; font-weight: bold; border-left: 4px solid #70c5d3; }
        .menu-bottom { margin-top: auto; border-top: 1px solid #eee; }
        
    </style>
    

    {{-- ไฟล์ CSS เฉพาะหน้า (ถ้ามี) ให้แต่ละหน้าใส่เพิ่มตรงนี้ได้ --}}
    @yield('styles')
</head>

<body>

    <!-- Header -->
    <header class="header">

                <!-- Hamburger -->
        <button class="menu-btn" id="menuBtn" aria-label="เปิดเมนู">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Logo -->
        <div class="logo">
            KKU FitCom
        </div>

        <!-- Search: ไปหน้าค้นหากิจกรรม (ถ้ามีแล้ว) -->
        <a class="search-btn" href="{{ Route::has('activities.index') ? route('activities.index') : '#' }}" aria-label="ค้นหา">
            <div class="search-icon"></div>
        </a>

    </header>

    @include('layouts.partials.menu')


    <!-- เนื้อหาของแต่ละหน้า -->
    <main class="content">
        @yield('content')
    </main>

    <script>
    (() => {
        const menu = document.getElementById('sideMenu');
        const overlay = document.getElementById('menuOverlay');
        const toggle = (open) => {
            menu.classList.toggle('open', open);
            overlay.classList.toggle('open', open);
        };
        document.getElementById('menuBtn').addEventListener('click', () => toggle(true));
        document.getElementById('menuClose').addEventListener('click', () => toggle(false));
        overlay.addEventListener('click', () => toggle(false));
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') toggle(false); });
    })();
    </script>
    {{-- JavaScript เฉพาะหน้า (เช่น Chart.js ของหน้า Dashboard) ใส่ตรงนี้ --}}
    @yield('scripts')

</body>
</html>