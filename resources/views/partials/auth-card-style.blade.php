<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --brand: #70c5d3;
        --brand-dark: #3a9fb0;
        --brand-deep: #1f6f7e;
        --brand-soft: #e6f6f9;
        --ink: #1e2a2e;
        --muted: #6b7b80;
        --line: #d9e6e9;
        --danger: #d9534f;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Noto Sans Thai', 'Segoe UI', Arial, sans-serif;
        color: var(--ink);
        min-height: 100vh;
        background: linear-gradient(160deg, #e6f6f9 0%, #f7fbfc 55%, #fff4e0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 16px;
    }
    .card {
        width: 100%;
        max-width: 420px;
        background: #fff;
        border-radius: 20px;
        padding: 36px 32px 32px;
        box-shadow: 0 10px 40px rgba(31, 111, 126, .12), 0 1px 3px rgba(0, 0, 0, .06);
    }
    .brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: var(--brand-deep);
        text-decoration: none;
        font-weight: 900;
        font-size: 28px;
        margin-bottom: 24px;
    }
    .brand-mark {
        width: 38px; height: 38px;
        border-radius: 11px;
        background: var(--brand);
        display: grid; place-items: center;
    }
    .brand-mark svg { width: 22px; height: 22px; }
    .card h1 { font-size: 24px; font-weight: 700; text-align: center; margin-bottom: 6px; }
    .card .sub { color: var(--muted); font-size: 14px; text-align: center; margin-bottom: 26px; }

    .alert { padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 18px; }
    .alert.ok { background: #e6f7ec; color: #2e7d4f; }
    .alert.err { background: #fdeaea; color: var(--danger); }

    .field { margin-bottom: 16px; }
    .field label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; }
    .input-wrap { position: relative; }
    .input-wrap input {
        width: 100%;
        height: 46px;
        padding: 0 14px;
        font: inherit;
        font-size: 15px;
        color: var(--ink);
        background: #fff;
        border: 1.5px solid var(--line);
        border-radius: 12px;
        transition: border-color .15s, box-shadow .15s;
    }
    .input-wrap input::placeholder { color: #a5b3b7; }
    .input-wrap input:focus {
        outline: none;
        border-color: var(--brand-dark);
        box-shadow: 0 0 0 4px rgba(112, 197, 211, .25);
    }
    .input-wrap input.invalid { border-color: var(--danger); }
    .input-wrap.has-toggle input { padding-right: 70px; }
    .toggle-pw {
        position: absolute;
        right: 5px; top: 5px;
        height: 36px;
        padding: 0 12px;
        border: none;
        background: transparent;
        color: var(--brand-dark);
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border-radius: 8px;
    }
    .toggle-pw:hover { background: var(--brand-soft); }
    .field-err { color: var(--danger); font-size: 13px; margin-top: 6px; }

    .row { display: flex; align-items: center; justify-content: space-between; margin: 4px 0 22px; font-size: 14px; }
    .remember { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
    .remember input { width: 17px; height: 17px; accent-color: var(--brand-dark); }
    .row a, .switch a { color: var(--brand-dark); font-weight: 600; text-decoration: none; }
    .row a:hover, .switch a:hover { text-decoration: underline; }

    .submit {
        width: 100%;
        height: 48px;
        margin-top: 6px;
        border: none;
        border-radius: 12px;
        background: var(--brand-dark);
        color: #fff;
        font: inherit;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, transform .05s;
    }
    .row + .submit { margin-top: 0; }
    .submit:hover { background: var(--brand-deep); }
    .submit:active { transform: translateY(1px); }
    .submit:disabled { opacity: .6; cursor: default; }
    .switch { text-align: center; margin-top: 22px; font-size: 14px; color: var(--muted); }

    @media (max-width: 480px) {
        body { align-items: flex-start; padding-top: 24px; }
        .card { padding: 28px 20px 24px; }
    }
</style>
