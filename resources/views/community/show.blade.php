{{-- ใช้ layout หลักของทีม (มีเมนูข้าง) --}}
@extends('layouts.site')

{{-- ชื่อแท็บเบราว์เซอร์ --}}
@section('title', $community->name . ' - KKU FitCom')

{{-- CSS เฉพาะหน้านี้ (ใช้โทนสีเดียวกับหน้า activities) --}}
@section('styles')
<style>
    .cm-wrap { max-width: 900px; margin: 0 auto; }
    .cm-card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 16px; }
    .cm-cover { width: 100%; max-height: 260px; object-fit: cover; border-radius: 12px; margin-bottom: 12px; }
    .cm-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 12px; background: #e6f6f9; color: #3a9fb0; }
    .cm-badge.private { background: #fdecec; color: #c0392b; }
    .btn { display: inline-block; padding: 8px 16px; border-radius: 8px; background: #70c5d3; color: #fff; border: none; cursor: pointer; font-size: 14px; text-decoration: none; }
    .btn.outline { background: #fff; color: #3a9fb0; border: 1px solid #70c5d3; }
    .btn.danger { background: #e57373; }
    .btn.small { padding: 4px 10px; font-size: 12px; }
    .alert { padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; }
    .alert.success { background: #e8f8ee; color: #1e7e46; }
    .alert.error { background: #fdecec; color: #c0392b; }
    .row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 0; border-bottom: 1px solid #eee; }
    .row:last-child { border-bottom: none; }
    .muted { color: #888; font-size: 13px; }
    textarea { width: 100%; min-height: 80px; padding: 8px; border: 1px solid #ccc; border-radius: 8px; font-family: inherit; }
</style>
@endsection

@section('content')
<div class="cm-wrap">

    <a href="{{ route('community.index') }}" class="muted">← กลับไปหน้ารวม Community</a>

    {{-- ===== ข้อความแจ้งผล (มาจาก ->with('success') / ->with('error') ใน Controller) ===== --}}
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    {{-- ===== ข้อมูลกลุ่ม ===== --}}
    <div class="cm-card">
        {{-- รูปปก (ถ้ามี) --}}
        @if ($community->cover_image)
            <img src="{{ asset('storage/' . $community->cover_image) }}" alt="{{ $community->name }}" class="cm-cover">
        @endif

        <h1>{{ $community->name }}</h1>

        <p>
            {{-- ป้าย public / private --}}
            <span class="cm-badge {{ $community->privacy === 'private' ? 'private' : '' }}">
                {{ $community->privacy === 'private' ? '🔒 Private' : '🌐 Public' }}
            </span>
            {{-- ?-> กัน error ถ้ายังไม่ได้เลือกกีฬา --}}
            <span class="cm-badge">{{ $community->sport?->name ?? 'ไม่ระบุกีฬา' }}</span>
        </p>

        <p>{{ $community->description ?: 'ยังไม่มีคำอธิบาย' }}</p>

        <p class="muted">
            สร้างโดย {{ $community->creator->name }}
            · สมาชิก {{ $community->approvedMembers->count() }} คน
        </p>

        {{-- ===== ปุ่มตามสถานะของผู้ใช้ ===== --}}
        @if ($isOwner)
            {{-- 1) เป็นเจ้าของกลุ่ม --}}
            <span class="cm-badge">👑 คุณเป็นเจ้าของกลุ่มนี้</span>

        @elseif ($isApprovedMember)
            {{-- 2) เป็นสมาชิกแล้ว → ออกจากกลุ่มได้ --}}
            <form method="POST" action="{{ route('community.leave', $community) }}"
                  onsubmit="return confirm('ต้องการออกจากกลุ่มนี้ใช่ไหม?')">
                @csrf {{-- token กันการปลอมคำขอ ต้องมีทุกฟอร์ม POST --}}
                <span class="cm-badge">✅ คุณเป็นสมาชิกแล้ว</span>
                <button type="submit" class="btn outline small">ออกจากกลุ่ม</button>
            </form>

        @elseif ($membership && $membership->status === 'pending')
            {{-- 3) ส่งคำขอแล้ว รออนุมัติ → ยกเลิกคำขอได้ (ใช้ route leave เดียวกัน) --}}
            <form method="POST" action="{{ route('community.leave', $community) }}">
                @csrf
                <span class="cm-badge">⏳ รอเจ้าของกลุ่มอนุมัติ</span>
                <button type="submit" class="btn outline small">ยกเลิกคำขอ</button>
            </form>

        @else
            {{-- 4) ยังไม่เป็นสมาชิก → ปุ่มเข้าร่วม (ข้อความต่างกันตาม privacy) --}}
            <form method="POST" action="{{ route('community.join', $community) }}">
                @csrf
                <button type="submit" class="btn">
                    {{ $community->privacy === 'private' ? 'ขอเข้าร่วมกลุ่ม' : 'เข้าร่วมกลุ่ม' }}
                </button>
            </form>
        @endif
    </div>

    {{-- ===== คำขอที่รออนุมัติ (owner เห็นคนเดียว) ===== --}}
    @if ($isOwner)
        <div class="cm-card">
            <h2>คำขอเข้าร่วม ({{ $pendingMembers->count() }})</h2>

            @forelse ($pendingMembers as $member)
                <div class="row">
                    <span>{{ $member->user->name }}</span>
                    <span>
                        {{-- ปุ่มอนุมัติ: ส่ง id ของกลุ่ม + id แถวสมาชิก --}}
                        <form method="POST" action="{{ route('community.approve', [$community, $member]) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn small">อนุมัติ</button>
                        </form>
                        {{-- ปุ่มปฏิเสธ --}}
                        <form method="POST" action="{{ route('community.reject', [$community, $member]) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn danger small">ปฏิเสธ</button>
                        </form>
                    </span>
                </div>
            @empty
                <p class="muted">ไม่มีคำขอที่รออนุมัติ</p>
            @endforelse
        </div>
    @endif

    {{-- ===== กิจกรรมที่กำลังจะจัด (ข้อมูลจากโมดูลของคนที่ 3) ===== --}}
    <div class="cm-card">
        <h2>กิจกรรมที่กำลังจะจัด</h2>

        @forelse ($community->activities as $activity)
            <div class="row">
                <a href="{{ route('activities.show', $activity) }}">{{ $activity->name }}</a>
                <span class="muted">{{ $activity->date }} · {{ $activity->start_time }}</span>
            </div>
        @empty
            <p class="muted">ยังไม่มีกิจกรรม</p>
        @endforelse
    </div>

    {{-- ===== รายชื่อสมาชิก (เฉพาะที่อนุมัติแล้ว) ===== --}}
    <div class="cm-card">
        <h2>สมาชิก</h2>

        @foreach ($community->approvedMembers as $member)
            <div class="row">
                <span>{{ $member->user->name }}</span>
                <span class="muted">{{ $member->role === 'owner' ? '👑 เจ้าของ' : 'สมาชิก' }}</span>
            </div>
        @endforeach
    </div>

    {{-- ===== โพสต์ในกลุ่ม ===== --}}
    <div class="cm-card">
        <h2>โพสต์</h2>

        @if ($isApprovedMember)
            {{-- ฟอร์มเขียนโพสต์ --}}
            <form method="POST" action="{{ route('community.posts.store', $community) }}">
                @csrf
                {{-- old() = คืนข้อความเดิมถ้า validate ไม่ผ่าน --}}
                <textarea name="content" placeholder="เขียนอะไรบางอย่างถึงสมาชิกในกลุ่ม...">{{ old('content') }}</textarea>

                {{-- แสดง error ของช่อง content (เช่น ปล่อยว่าง) --}}
                @error('content')
                    <div class="alert error">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn">โพสต์</button>
            </form>

            <hr>

            {{-- รายการโพสต์ ใหม่สุดอยู่บน --}}
            @forelse ($posts as $post)
                <div class="row" style="display:block">
                    <strong>{{ $post->user->name }}</strong>
                    <span class="muted">· {{ \Illuminate\Support\Carbon::parse($post->created_at)->format('d/m/Y H:i') }}</span>
                    <p>{{ $post->content }}</p> {{-- {{ }} escape HTML ให้อัตโนมัติ กัน XSS --}}
                </div>
            @empty
                <p class="muted">ยังไม่มีโพสต์ เริ่มโพสต์แรกเลย!</p>
            @endforelse
        @else
            {{-- คนนอก / รออนุมัติ --}}
            <p class="muted">🔒 เข้าร่วมกลุ่มเพื่อดูและเขียนโพสต์</p>
        @endif
    </div>

</div>
@endsection