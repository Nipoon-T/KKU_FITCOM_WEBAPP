@extends('layouts.site')

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding: 20px;">

    <h1>ยินดีต้อนรับสู่ KKU FitCom</h1>
    <p>ค้นหาเพื่อนออกกำลังกาย เข้าร่วมกิจกรรม และติดตามความก้าวหน้าของคุณ</p>

    <div style="margin-top: 20px;">
        <a href="{{ route('register') }}" style="padding: 10px 20px; background: #4FC3D9; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">
            สมัครสมาชิก
        </a>
        <a href="{{ route('login') }}" style="padding: 10px 20px; background: #eee; color: #333; text-decoration: none; border-radius: 4px;">
            เข้าสู่ระบบ
        </a>
    </div>

    <hr style="margin: 30px 0;">

    <h2>ประเภทกีฬา</h2>
    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
        @forelse ($sports as $sport)
            <span style="border: 1px solid #ccc; padding: 6px 12px; border-radius: 20px;">
                {{ $sport->name }}
            </span>
        @empty
            <p>ยังไม่มีข้อมูลกีฬา</p>
        @endforelse
    </div>

    <hr style="margin: 30px 0;">

    <h2>ชุมชนล่าสุด</h2>
    <div style="margin-top: 10px;">
        @forelse ($communities as $community)
            <div style="border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-bottom: 10px;">
                <strong>{{ $community->name }}</strong>
                <p style="color: #666; margin-top: 5px;">{{ $community->description }}</p>
            </div>
        @empty
            <p>ยังไม่มีชุมชน</p>
        @endforelse
    </div>

    <hr style="margin: 30px 0;">

    <h2>กิจกรรมที่กำลังจะมาถึง</h2>
    <div style="margin-top: 10px;">
        @forelse ($activities as $activity)
            <div style="border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-bottom: 10px;">
                <strong>{{ $activity->name }}</strong>
                <p style="color: #666; margin-top: 5px;">
                    วันที่ {{ $activity->date->format('d/m/Y') }} เวลา {{ $activity->start_time }} - {{ $activity->end_time }}
                </p>
            </div>
        @empty
            <p>ยังไม่มีกิจกรรม</p>
        @endforelse
    </div>

</div>
@endsection