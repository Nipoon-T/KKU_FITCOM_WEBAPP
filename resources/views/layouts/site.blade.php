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
    </style>

    {{-- ไฟล์ CSS เฉพาะหน้า (ถ้ามี) ให้แต่ละหน้าใส่เพิ่มตรงนี้ได้ --}}
    @yield('styles')
</head>

<body>

    <!-- Header -->
    <header class="header">

        <!-- Hamburger -->
        <button class="menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Logo -->
        <div class="logo">
            KKU FitCom
        </div>

        <!-- Search -->
        <button class="search-btn">
            <div class="search-icon"></div>
        </button>

    </header>


    <!-- เนื้อหาของแต่ละหน้า -->
    <main class="content">
        @yield('content')
    </main>

    {{-- JavaScript เฉพาะหน้า (เช่น Chart.js ของหน้า Dashboard) ใส่ตรงนี้ --}}
    @yield('scripts')

</body>
</html>