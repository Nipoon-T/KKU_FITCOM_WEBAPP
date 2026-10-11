@extends('layouts.site')

@section('title', 'จัดการเหรียญ: '.$user->name.' - KKU FitCom')

@section('styles')
<style>
    .dash { max-width: 960px; margin: 24px auto; padding: 0 16px; }
    .back { color: #3a9fb0; text-decoration: none; font-size: 14px; }
    .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin: 16px 0 24px; }
    .card h1 { font-size: 22px; margin-bottom: 4px; }
    .muted { color: #777; font-size: 14px; }
    .badges { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; }
    .badge { text-align: center; padding: 16px 10px; border-radius: 12px; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .badge .icon { font-size: 40px; }
    .badge .name { font-weight: bold; margin-top: 6px; font-size: 14px; }
    .badge .desc { font-size: 12px; color: #777; margin-top: 4px; min-height: 32px; }
    .badge .when { font-size: 12px; margin: 8px 0; color: #2e9e5b; }
    .badge.locked .icon, .badge.locked .name { opacity: .4; filter: grayscale(1); }
    .badge.locked .when { color: #999; }
    .tag { display: inline-block; font-size: 11px; padding: 1px 8px; border-radius: 8px; background: #fff3d6; color: #b07a00; margin-top: 4px; }
    .badge-toggle { border: none; border-radius: 8px; padding: 6px 14px; cursor: pointer; font-family: inherit; background: #70c5d3; color: #fff; }
    .badge.earned .badge-toggle { background: #f3d6d6; color: #b33; }
    .badge-toggle:disabled { opacity: .5; cursor: wait; }
</style>
@endsection

@section('content')
<div class="dash">
    <a class="back" href="{{ route('admin.dashboard') }}">← กลับหน้า Admin</a>

    <div class="card">
        <h1>{{ $user->name }}</h1>
        <div class="muted">{{ $user->email }} · {{ number_format($user->totalPoints()) }} แต้ม · ได้เหรียญ {{ $earned->count() }} / {{ $badges->count() }}</div>
    </div>

    <div class="badges">
        @foreach ($badges as $badge)
            @php $has = $earned->has($badge->id); @endphp
            <div class="badge {{ $has ? 'earned' : 'locked' }}">
                <div class="icon">{{ $badge->icon }}</div>
                <div class="name">{{ $badge->name }}</div>
                @if ($badge->condition_type === 'manual')
                    <span class="tag">เหรียญพิเศษ</span>
                @endif
                <div class="desc">{{ $badge->description }}</div>
                <div class="when">
                    {{ $has ? 'ได้เมื่อ '.\Carbon\CarbonImmutable::parse($earned[$badge->id])->format('d/m/Y') : 'ยังไม่ได้รับ' }}
                </div>
                <button class="badge-toggle" data-url="{{ route('admin.users.badges.store', [$user, $badge]) }}">
                    {{ $has ? 'ยึดคืน' : 'มอบเหรียญ' }}
                </button>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
    const csrf = @json(csrf_token());

    document.querySelectorAll('.badge-toggle').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const card = btn.closest('.badge');
            const earned = card.classList.contains('earned');

            if (earned && ! confirm('ยืนยันยึดเหรียญนี้คืน?')) return;

            btn.disabled = true;
            try {
                const res = await fetch(btn.dataset.url, {
                    method: earned ? 'DELETE' : 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                if (! res.ok) throw new Error();
                const data = await res.json();

                card.classList.toggle('earned', data.earned);
                card.classList.toggle('locked', ! data.earned);
                btn.textContent = data.earned ? 'ยึดคืน' : 'มอบเหรียญ';
                card.querySelector('.when').textContent = data.earned ? 'ได้เมื่อ ' + data.earned_at : 'ยังไม่ได้รับ';
            } catch (e) {
                alert('ทำรายการไม่สำเร็จ ลองใหม่อีกครั้ง');
            } finally {
                btn.disabled = false;
            }
        });
    });
</script>
@endsection