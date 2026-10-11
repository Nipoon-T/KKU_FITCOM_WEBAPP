@extends('layouts.site')

@section('title', 'เหรียญพิเศษ - Admin - KKU FitCom')

@section('styles')
<style>
    .dash { max-width: 860px; margin: 24px auto; padding: 0 16px; }
    .back { color: #3a9fb0; text-decoration: none; font-size: 14px; }
    .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin: 16px 0; }
    .card h2 { font-size: 18px; margin-bottom: 12px; }
    .form-row { display: grid; grid-template-columns: 90px 1fr; gap: 10px; margin-bottom: 10px; }
    .form-row input { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }
    .form-row .icon-input { font-size: 22px; text-align: center; }
    .btn { padding: 8px 18px; border: none; border-radius: 8px; background: #70c5d3; color: #fff; cursor: pointer; font-family: inherit; }
    .btn-del { background: #f3d6d6; color: #b33; padding: 6px 12px; }
    .alert { padding: 10px 14px; border-radius: 8px; margin-top: 12px; font-size: 14px; }
    .alert.ok { background: #e3f4e9; color: #2e7d4f; }
    .alert.err { background: #fbe3e3; color: #b33; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 14px; }
    .big { font-size: 28px; width: 50px; }
    .muted { color: #777; font-size: 13px; }
</style>
@endsection

@section('content')
<div class="dash">
    <a class="back" href="{{ route('admin.dashboard') }}">← กลับหน้า Admin</a>

    @if (session('status'))
        <div class="alert ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert err">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <h2>สร้างเหรียญพิเศษใหม่</h2>
        <form method="POST" action="{{ route('admin.badges.store') }}">
            @csrf
            <div class="form-row">
                <input class="icon-input" name="icon" value="{{ old('icon') }}" placeholder="🏅" maxlength="10">
                <input name="name" value="{{ old('name') }}" placeholder="ชื่อเหรียญ เช่น นักวิ่งสายฝน" maxlength="50">
            </div>
            <div class="form-row">
                <span></span>
                <input name="description" value="{{ old('description') }}" placeholder="คำอธิบาย เช่น รางวัลพิเศษ: มาวิ่งแม้ฝนตก" maxlength="255">
            </div>
            <div class="muted" style="margin-bottom:12px">ไอคอนใช้ emoji ได้เลย กด Win + . เพื่อเปิดแป้น emoji บน Windows</div>
            <button class="btn" type="submit">สร้างเหรียญ</button>
        </form>
    </div>

    <div class="card">
        <h2>เหรียญพิเศษทั้งหมด ({{ $badges->count() }})</h2>
        @if ($badges->isEmpty())
            <p class="muted">ยังไม่มีเหรียญพิเศษ</p>
        @else
            <table>
                @foreach ($badges as $badge)
                    <tr>
                        <td class="big">{{ $badge->icon }}</td>
                        <td>
                            <strong>{{ $badge->name }}</strong><br>
                            <span class="muted">{{ $badge->description }}</span>
                        </td>
                        <td class="muted">มอบไปแล้ว {{ $badge->users_count }} คน</td>
                        <td>
                            <form method="POST" action="{{ route('admin.badges.destroy', $badge) }}"
                                  onsubmit="return confirm('ลบเหรียญนี้? คนที่ได้ไปแล้ว {{ $badge->users_count }} คนจะเสียเหรียญนี้ด้วย')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-del" type="submit">ลบ</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
</div>
@endsection