@extends('layouts.site')

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding: 20px;">

    <h1>ยินดีต้อนรับสู่ KKU FitCom</h1>
    <p>ค้นหาเพื่อนออกกำลังกาย เข้าร่วมกิจกรรม และติดตามความก้าวหน้าของคุณ</p>

    <div style="margin-top: 15px;">
        <input type="text" placeholder="ค้นหากิจกรรม & คอมมูนิตี้ที่คุณชอบ"
            style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 25px; font-size: 14px;">
    </div>

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

    <h2>คอมมูนิตี้มาแรง</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; margin-top: 10px;">
        @forelse ($communities as $community)
            <div style="border: 1px solid #eee; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="height: 90px; background: #81C784; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    {{ $community->sport->name ?? 'ชุมชน' }}
                </div>
                <div style="padding: 10px;">
                    <strong style="display: block; font-size: 14px; margin-bottom: 4px;">{{ $community->name }}</strong>
                    <p style="color: #666; font-size: 12px; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $community->description }}
                    </p>
                    <a href="#" style="display: inline-block; padding: 5px 12px; background: #4FC3D9; color: white; text-decoration: none; border-radius: 20px; font-size: 12px;">
                        เข้าดู
                    </a>
                </div>
            </div>
        @empty
            <p>ยังไม่มีชุมชน</p>
        @endforelse
    </div>

    <hr style="margin: 30px 0;">

    <h2>กิจกรรมที่น่าสนใจ</h2>
    <div style="display: flex; gap: 12px; overflow-x: auto; padding: 10px 0; margin-top: 10px;">
        @forelse ($activities as $activity)
            <div style="min-width: 220px; border: 1px solid #eee; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="height: 100px; background: #FFB74D; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    {{ $activity->sport->name ?? 'กิจกรรม' }}
                </div>
                <div style="padding: 12px;">
                    <strong style="display: block; margin-bottom: 5px;">{{ $activity->name }}</strong>
                    <p style="color: #666; font-size: 13px; margin-bottom: 10px;">
                        {{ $activity->date->format('d/m/Y') }} · {{ $activity->start_time }} - {{ $activity->end_time }}
                    </p>
                    <a href="#" style="display: inline-block; padding: 6px 14px; background: #4FC3D9; color: white; text-decoration: none; border-radius: 20px; font-size: 13px;">
                        ดูรายละเอียด
                    </a>
                </div>
            </div>
        @empty
            <p>ยังไม่มีกิจกรรม</p>
        @endforelse
    </div>

</div>
@endsection