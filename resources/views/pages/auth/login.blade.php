<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - KKU FitCom</title>
    @include('partials.auth-card-style')
</head>
<body>
    <main class="card">
        <h1>เข้าสู่ระบบ</h1>
        <p class="sub">กรอกอีเมลและรหัสผ่านเพื่อเข้าใช้งาน</p>

        @if (session('status'))
            <div class="alert ok">{{ session('status') }}</div>
        @endif

        @if ($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
            <div class="alert err">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" data-busy="กำลังเข้าสู่ระบบ...">
            @csrf

            <div class="field">
                <label for="email">อีเมล</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        placeholder="email@example.com" autocomplete="email" required autofocus
                        class="{{ $errors->has('email') ? 'invalid' : '' }}">
                </div>
                @error('email') <p class="field-err">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password">รหัสผ่าน</label>
                <div class="input-wrap has-toggle">
                    <input type="password" id="password" name="password"
                        placeholder="รหัสผ่านของคุณ" autocomplete="current-password" required
                        class="{{ $errors->has('password') ? 'invalid' : '' }}">
                    <button type="button" class="toggle-pw" data-target="password" aria-label="แสดงหรือซ่อนรหัสผ่าน">แสดง</button>
                </div>
                @error('password') <p class="field-err">{{ $message }}</p> @enderror
            </div>

            <div class="row">
                <label class="remember">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    จดจำฉันไว้
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">ลืมรหัสผ่าน?</a>
                @endif
            </div>

            <button type="submit" class="submit" data-test="login-button">เข้าสู่ระบบ</button>
        </form>

        @if (Route::has('register'))
            <p class="switch">ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิก</a></p>
        @endif
    </main>

    <script>
        document.querySelectorAll('.toggle-pw').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.textContent = show ? 'ซ่อน' : 'แสดง';
            });
        });

        document.querySelectorAll('form[data-busy]').forEach((form) => {
            form.addEventListener('submit', () => {
                const btn = form.querySelector('.submit');
                btn.disabled = true;
                btn.textContent = form.dataset.busy;
            });
        });
    </script>
</body>
</html>
