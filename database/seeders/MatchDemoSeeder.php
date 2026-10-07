<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Location;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Seeder;

class MatchDemoSeeder extends Seeder
{
    // ข้อมูลตัวอย่างสำหรับทดสอบคะแนน Matching (รันซ้ำได้ ไม่สร้างซ้ำ)
    public function run(): void
    {
        // กีฬา
        $run = Sport::firstOrCreate(['name' => 'วิ่ง']);
        $football = Sport::firstOrCreate(['name' => 'ฟุตบอล']);
        Sport::firstOrCreate(['name' => 'โยคะ']);

        // สถานที่
        $stadium = Location::firstOrCreate(['name' => 'สนามกีฬา มข']);
        $mall = Location::firstOrCreate(['name' => 'ห้างในเมือง']);

        // ผู้ใช้ A (คนที่จะดูคำแนะนำ)
        $userA = User::firstOrCreate(
            ['email' => 'demo.a@example.com'],
            ['name' => 'Demo A', 'password' => 'password']
        );
        $userA->profile()->updateOrCreate(
            ['user_id' => $userA->id],
            ['goal' => 'ลดน้ำหนัก', 'skill_level' => 'เริ่มต้น', 'preferred_location' => 'มข']
        );
        $userA->sports()->sync([$run->id]);

        // เวลาว่างของ A: วันจันทร์ (1) ช่วงเช้าช่วงเดียว
        $userA->availabilities()->delete();
        $userA->availabilities()->create(['day_of_week' => 1, 'time_slot' => '06:00-11:59']);

        // ผู้ใช้ B (ผู้สร้างกิจกรรม)
        $userB = User::firstOrCreate(
            ['email' => 'demo.b@example.com'],
            ['name' => 'Demo B', 'password' => 'password']
        );

        // กิจกรรม 1: วันจันทร์ 2026-11-02 เช้า ตรงกับเวลาว่างของ A
        Activity::updateOrCreate(
            ['name' => 'Demo วิ่งเช้า มข', 'created_by' => $userB->id],
            [
                'sport_id' => $run->id,
                'location_id' => $stadium->id,
                'skill_level' => 1,
                'date' => '2026-11-02',
                'start_time' => '07:00',
                'end_time' => '08:00',
                'max_participants' => 20,
            ]
        );

        // กิจกรรม 2: วันอังคารเย็น ไม่ตรงเวลาว่าง
        Activity::updateOrCreate(
            ['name' => 'Demo ฟุตบอลในเมือง', 'created_by' => $userB->id],
            [
                'sport_id' => $football->id,
                'location_id' => $mall->id,
                'skill_level' => 3,
                'date' => '2026-11-03',
                'start_time' => '18:00',
                'end_time' => '19:30',
                'max_participants' => 20,
            ]
        );

        // กิจกรรม 3: วันพุธเย็น ไม่ตรงเวลาว่าง
        Activity::updateOrCreate(
            ['name' => 'Demo วิ่งในเมือง', 'created_by' => $userB->id],
            [
                'sport_id' => $run->id,
                'location_id' => $mall->id,
                'skill_level' => 3,
                'date' => '2026-11-04',
                'start_time' => '18:00',
                'end_time' => '19:30',
                'max_participants' => 20,
            ]
        );
    }
}
