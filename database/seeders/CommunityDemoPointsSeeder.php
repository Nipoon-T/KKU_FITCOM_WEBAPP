<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Attendance;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Location;
use App\Models\Sport;
use App\Services\GamificationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CommunityDemoPointsSeeder extends Seeder
{
    // สุ่มคะแนนสมาชิกแต่ละคนในแต่ละคอมมู 0-100 ตามกติกาโปรเจกต์:
    // เช็คชื่อ 1 กิจกรรม = 10 แต้ม (GamificationService::POINTS_PER_ACTIVITY)
    // จึงต้องสร้างกิจกรรมที่ผ่านมาแล้ว + ลงชื่อ + เช็คชื่อ (Attendance)
    // แล้ว AttendanceObserver จะบันทึก point_logs / streak / เหรียญให้เอง
    // รันด้วย: php artisan db:seed --class=CommunityDemoPointsSeeder
    public function run(): void
    {
        $communities = Community::query()->orderBy('id')->get();
        $defaultSportId = Sport::query()->orderBy('id')->value('id');

        foreach ($communities as $index => $community) {
            // จำนวนกิจกรรมที่ผ่านมาแล้วของคอมมูนี้ (6-10) = คะแนนสูงสุดที่ใครจะได้ 60-100
            $pastCount = 6 + ($index % 5);

            // สร้างกิจกรรมย้อนหลัง ห่างกันครั้งละ 2 วัน (เก่าสุด -> ใหม่สุด)
            $pastActivities = [];
            for ($n = 1; $n <= $pastCount; $n++) {
                $date = now()->subDays(($pastCount - $n + 1) * 2);

                $pastActivities[] = Activity::firstOrCreate(
                    ['community_id' => $community->id, 'name' => 'ซ้อมครั้งที่ '.$n.' - '.$community->name],
                    [
                        'description' => 'กิจกรรมที่จัดไปแล้ว ใช้แสดงผลคะแนนสะสม',
                        'sport_id' => $community->sport_id ?? $defaultSportId,
                        'location_id' => $this->locationId($community),
                        'skill_level' => 1 + ($n % 3),
                        'date' => $date->toDateString(),
                        'start_time' => '17:00',
                        'end_time' => '18:00',
                        'max_participants' => 30,
                        'created_by' => $community->created_by,
                    ]
                );
            }

            $memberIds = CommunityMember::query()
                ->where('community_id', $community->id)
                ->where('status', 'approved')
                ->pluck('user_id');

            foreach ($memberIds as $userId) {
                // สุ่มแบบคงที่จาก id (รันซ้ำแล้วได้ค่าเดิม): จำนวนกิจกรรมที่เข้าร่วม 0..pastCount
                $attended = crc32('c'.$community->id.'u'.$userId) % ($pastCount + 1);

                foreach (array_slice($pastActivities, 0, $attended) as $activity) {
                    $participant = ActivityParticipant::firstOrCreate(
                        ['activity_id' => $activity->id, 'user_id' => $userId],
                        ['status' => 'registered']
                    );

                    // ถ้าเช็คชื่อไปแล้ว ข้าม (กันได้แต้มซ้ำ)
                    if (Attendance::where('activity_participant_id', $participant->id)->exists()) {
                        continue;
                    }

                    // สร้าง Attendance -> observer ให้ +10 แต้มเอง
                    Attendance::create([
                        'activity_participant_id' => $participant->id,
                        'checked_in_at' => Carbon::parse($activity->date)->setTime(18, 0),
                        'checked_by' => $community->created_by,
                    ]);
                }
            }
        }

        $this->command->info('สุ่มคะแนนสมาชิกเสร็จ (1 กิจกรรม = '.GamificationService::POINTS_PER_ACTIVITY.' แต้ม)');
    }

    // เลือกสถานที่แบบคงที่จาก id ของคอมมู
    private function locationId(Community $community): ?int
    {
        $ids = Location::query()->orderBy('id')->pluck('id');

        return $ids->isEmpty() ? null : $ids[$community->id % $ids->count()];
    }
}
