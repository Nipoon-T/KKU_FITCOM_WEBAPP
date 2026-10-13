@extends('layouts.site')

@section('title', $community->name.' - KKU FitCom')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/community.css') }}">
@endsection

@section('content')
<div class="cm-wrap narrow">
    <a href="{{ route('community.index') }}" class="back-link" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">← กลับ</a>

    {{-- ข้อความแจ้งผล --}}
    @if (session('success'))
        <p class="flash-ok">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p class="flash-err">{{ session('error') }}</p>
    @endif

    {{-- รูปปกใหญ่ (เหมือนหน้ารายละเอียดกิจกรรม) --}}
    @if ($community->cover_image)
        <img src="{{ asset('storage/'.$community->cover_image) }}" alt="{{ $community->name }}" class="cover-hero"
            style="object-position: {{ $community->cover_position ?? '50% 50%' }};">
    @endif

    {{-- ===== รายละเอียดกลุ่ม ===== --}}
    <div class="detail-card">
        <h1>{{ $community->name }}</h1>
        <p class="desc">{{ $community->description ?: 'ไม่มีคำอธิบาย' }}</p>

        <p>🏃 <strong>ประเภทกีฬา:</strong> {{ $community->sport?->name ?? 'ไม่ระบุ' }}</p>
        <p>{{ $community->privacy === 'private' ? '🔒' : '🌐' }} <strong>ความเป็นส่วนตัว:</strong>
            {{ $community->privacy === 'private' ? 'Private (ต้องรออนุมัติ)' : 'Public (เข้าร่วมได้ทันที)' }}</p>

    <div class="creator-card">
        @include('community._avatar', ['user' => $community->creator, 'size' => 56])
        <div>
            <p class="creator-label">ผู้สร้าง Community</p>
            <p class="creator-name"><strong>ชื่อ:</strong> {{ $community->creator->name }}</p>
        </div>
    </div>
        <p>👥 <strong>สมาชิก:</strong> {{ $community->approvedMembers->count() }} คน</p>
    </div>

    {{-- ===== กล่องสถานะ (เหมือนกล่อง "เข้าร่วมกิจกรรม") ===== --}}
    <div class="detail-card status-box">
        @if ($isOwner)
            <p class="status">คุณเป็นเจ้าของกลุ่มนี้</p>

        @elseif ($isApprovedMember)
            <p class="status">✅ คุณเป็นสมาชิกแล้ว</p>
            <form method="POST" action="{{ route('community.leave', $community) }}"
                  onsubmit="return confirm('ต้องการออกจากกลุ่มนี้ใช่ไหม?')">
                @csrf
                <button type="submit" class="btn danger small">ออกจากกลุ่ม</button>
            </form>

        @elseif ($membership && $membership->status === 'pending')
            <p class="status">⏳ รอเจ้าของกลุ่มอนุมัติ</p>
            <form method="POST" action="{{ route('community.leave', $community) }}">
                @csrf
                <button type="submit" class="btn outline small">ยกเลิกคำขอ</button>
            </form>

        @else
            <form method="POST" action="{{ route('community.join', $community) }}">
                @csrf
                <button type="submit" class="btn big">
                    {{ $community->privacy === 'private' ? 'ขอเข้าร่วมกลุ่ม' : 'เข้าร่วมกลุ่ม' }}
                </button>
            </form>
        @endif
    </div>

    {{-- ===== สำหรับเจ้าของกลุ่ม ===== --}}
    @if ($isOwner)
        <div class="owner-box">
            <p class="label">สำหรับเจ้าของกลุ่ม</p>
            <a href="{{ route('community.edit', $community) }}" class="btn outline small">แก้ไขกลุ่ม</a>
            {{-- ลิงก์ไปหน้าสร้างกิจกรรม พร้อมเลือกกลุ่มนี้ไว้ให้ (หน้าของคนที่ 3 รองรับ ?community=) --}}
            <a href="{{ route('activities.create', ['community' => $community->id]) }}" class="btn outline small">+ สร้างกิจกรรมในกลุ่ม</a>
        </div>

        {{-- คำขอเข้าร่วม (AJAX: id/class ด้านล่างใช้กับ community.js) --}}
        <div class="detail-card">
            <h2>คำขอเข้าร่วม (<span id="pendingCount">{{ $pendingMembers->count() }}</span>)</h2>

            <div id="ajaxMessage" style="display:none"></div>

            @foreach ($pendingMembers as $member)
                <div class="list-row member-row">
                    <span>{{ $member->user->name }}</span>
                    <span>
                        <form method="POST" action="{{ route('community.approve', [$community, $member]) }}" class="js-member-action">
                            @csrf
                            <button type="submit" class="btn small">อนุมัติ</button>
                        </form>
                        <form method="POST" action="{{ route('community.reject', [$community, $member]) }}" class="js-member-action">
                            @csrf
                            <button type="submit" class="btn danger small">ปฏิเสธ</button>
                        </form>
                    </span>
                </div>
            @endforeach

            <p id="pendingEmpty" class="muted" style="display: {{ $pendingMembers->count() == 0 ? 'block' : 'none' }}">
                ไม่มีคำขอที่รออนุมัติ
            </p>
        </div>
    @endif

    {{-- ===== กิจกรรมที่กำลังจะจัด ===== --}}
<div class="detail-card">
        <h2>กิจกรรมที่กำลังจะจัด</h2>

        @if ($community->activities->isEmpty())
            <p class="muted">ยังไม่มีกิจกรรม</p>
        @else
            <div class="cm-grid">
                @foreach ($community->activities as $activity)
                    <div class="cm-card">
                        {{-- รูปกิจกรรม (ไม่มีรูป = กรอบเปล่า ขนาดเท่ากัน) --}}
                        @if ($activity->cover_image)
                            <img src="{{ asset('storage/'.$activity->cover_image) }}" alt="{{ $activity->name }}"
                                style="object-position: {{ $activity->cover_position ?? '50% 50%' }};">
                        @else
                            <div class="cm-cover-empty">🏃</div>
                        @endif

                        <h3>{{ $activity->name }}</h3>

                        <p><strong>ประเภทกิจกรรม:</strong> {{ $activity->sport?->name ?? '-' }}</p>
                        {{-- วันที่ภาษาไทย เช่น "5 กันยายน พ.ศ. 2569" (ปี ค.ศ. + 543 = พ.ศ.) --}}
                        <p><strong>วัน/เดือน/ปี:</strong>
                            {{ $activity->date->locale('th')->translatedFormat('j F') }} พ.ศ. {{ $activity->date->year + 543 }}</p>
                        <p><strong>เวลา:</strong> {{ substr($activity->start_time, 0, 5) }} น.</p>
                        <p><strong>สถานที่:</strong> {{ $activity->location?->name ?? '-' }}</p>
                        <p><strong>จำนวนคน:</strong> {{ $activity->registered_count }}/{{ $activity->max_participants }}</p>

                        {{-- ไปหน้ากิจกรรม (การเข้าร่วมจริงทำในหน้านั้น ของคนที่ 3) --}}
                        <a href="{{ route('activities.show', $activity) }}" class="btn center">เข้าร่วม</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ===== สมาชิก ===== --}}
    @php
        $members = $community->approvedMembers;
        $showLimit = 8;
    @endphp
    <div class="detail-card">
        <h2>สมาชิก ({{ $members->count() }})</h2>

        <div class="member-avatars">
            @foreach ($members->take($showLimit) as $member)
                @include('community._avatar', ['user' => $member->user, 'size' => 44])
            @endforeach

            {{-- เกิน 8 คน → วงกลมสีเทา "+จำนวนที่เหลือ" --}}
            @if ($members->count() > $showLimit)
                <span class="avatar avatar-more" style="width: 44px; height: 44px;">+{{ $members->count() - $showLimit }}</span>
            @endif
        </div>
    </div>

    {{-- ===== โพสต์ ===== --}}
    <div class="detail-card">
        <h2>โพสต์</h2>

        @if ($isApprovedMember)
            <form method="POST" action="{{ route('community.posts.store', $community) }}" class="post-form">
                @csrf
                <textarea name="content" placeholder="เขียนอะไรบางอย่างถึงสมาชิกในกลุ่ม...">{{ old('content') }}</textarea>
                @error('content')
                    <p class="flash-err">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn">โพสต์</button>
            </form>

            @forelse ($posts as $post)
                <div class="post">
                    <span class="author">{{ $post->user->name }}</span>
                    <span class="muted">· {{ \Illuminate\Support\Carbon::parse($post->created_at)->format('d/m/Y H:i') }}</span>
                    <p class="content">{{ $post->content }}</p>
                </div>
            @empty
                <p class="muted" style="margin-top:12px">ยังไม่มีโพสต์ เริ่มโพสต์แรกเลย!</p>
            @endforelse
        @else
            <p class="muted">🔒 เข้าร่วมกลุ่มเพื่อดูและเขียนโพสต์</p>
        @endif
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/community.js') }}"></script>
@endsection