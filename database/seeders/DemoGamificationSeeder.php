<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Attendance;
use App\Models\Location;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoGamificationSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'host@demo.test')->exists()) {
            $this->command->warn('มีข้อมูล demo อยู่แล้ว ข้ามการสร้าง');

            return;
        }

        $sport = Sport::firstOrCreate(['name' => 'วิ่ง']);
        $location = Location::create(['name' => 'สนามกีฬา มข. (demo)']);
        $host = User::factory()->create(['name' => 'Demo Host', 'email' => 'host@demo.test']);

        // days = เข้าร่วมเมื่อกี่วันก่อน (0 = วันนี้)
        $plans = [
            ['name' => 'น้องขยัน', 'email' => 'active@demo.test', 'days' => [20, 18, 15, 12, 10, 6, 5, 4, 3, 2, 1, 0]],
            ['name' => 'สายชิล', 'email' => 'casual@demo.test', 'days' => [13, 9, 8, 2]],
            ['name' => 'มือใหม่', 'email' => 'newbie@demo.test', 'days' => [0]],
            ['name' => 'เพิ่งสมัคร', 'email' => 'empty@demo.test', 'days' => []],
        ];

        /** @var array<int, Activity> $activities */
        $activities = [];

        foreach ($plans as $plan) {
            $user = User::factory()->create(['name' => $plan['name'], 'email' => $plan['email']]);

            $days = $plan['days'];
            rsort($days); // เรียงจากเก่าสุดไปใหม่สุด streak จะนับถูก

            foreach ($days as $daysAgo) {
                $date = now()->subDays($daysAgo);

                // กิจกรรมวันเดียวกันใช้ร่วมกัน ไม่สร้างซ้ำ
                $activities[$daysAgo] ??= Activity::create([
                    'name' => 'วิ่งเช้า demo '.$date->format('d/m'),
                    'sport_id' => $sport->id,
                    'location_id' => $location->id,
                    'date' => $date->toDateString(),
                    'start_time' => '17:00',
                    'end_time' => '18:00',
                    'max_participants' => 20,
                    'created_by' => $host->id,
                ]);

                $participant = ActivityParticipant::create([
                    'activity_id' => $activities[$daysAgo]->id,
                    'user_id' => $user->id,
                ]);

                // บรรทัดนี้จะทำให้ observer ทำงาน → ได้แต้ม/streak/เหรียญ
                Attendance::create([
                    'activity_participant_id' => $participant->id,
                    'checked_in_at' => $date->setTime(18, 0),
                    'checked_by' => $host->id,
                ]);
            }
        }

        $this->command->info('สร้างข้อมูล demo เสร็จ! login ด้วย active@demo.test / password');
    }
}
