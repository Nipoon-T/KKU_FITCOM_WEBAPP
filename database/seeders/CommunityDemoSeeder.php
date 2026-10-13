<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Location;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunityDemoSeeder extends Seeder
{
    // ข้อมูลเดโม่สำหรับโมดูล Community (รันซ้ำได้ ไม่สร้างซ้ำ)
    // รันด้วย: php artisan db:seed --class=CommunityDemoSeeder
    public function run(): void
    {
        // 1) สร้างผู้ใช้เดโม่ 20 คน (อีเมล demo.member1..20@example.com / รหัสผ่าน password)
        $names = [
            'สมชาย ใจดี', 'สมหญิง รักสุขภาพ', 'ปกรณ์ วิ่งไว', 'อรอุมา แข็งแรง', 'ธนากร ฟิตเนส',
            'พิมพ์ชนก ยืดเหยียด', 'กฤษฎา สายปั่น', 'นภัสสร ว่ายน้ำ', 'ภูวนาท ตบเบา', 'ชลธิชา โยคะ',
            'วรากร ยิงสาม', 'ศิรินทิพย์ แบดมินตัน', 'อนุชา เตะบอล', 'ปรียา เดินป่า', 'ณัฐวุฒิ ยกเหล็ก',
            'จิราพร เต้นแอโรบิก', 'พีรพัฒน์ ปีนผา', 'รัตนา เทนนิส', 'สุรชัย มวยไทย', 'มนัสวี ปั่นเช้า',
        ];

        $demoUsers = [];
        foreach ($names as $i => $name) {
            $demoUsers[] = User::firstOrCreate(
                ['email' => 'demo.member'.($i + 1).'@example.com'],
                ['name' => $name, 'password' => 'password']
            );
        }

        $sportIds = Sport::query()->orderBy('id')->pluck('id')->all();
        $locationIds = Location::query()->orderBy('id')->pluck('id')->all();

        // ถ้ายังไม่มีกีฬาหรือสถานที่ ให้หยุดและบอกวิธีแก้
        if ($sportIds === [] || $locationIds === []) {
            $this->command->warn('ยังไม่มีข้อมูล sports/locations ให้รัน SportSeeder และ LocationSeeder ก่อน');

            return;
        }

        // ชื่อกิจกรรมตัวอย่าง พร้อมรายละเอียดที่สอดคล้องกัน
        $activityTemplates = [
            ['title' => 'นัดซ้อมประจำสัปดาห์', 'desc' => 'มาออกกำลังกายด้วยกัน ทุกระดับเข้าร่วมได้ พบกันที่จุดนัดหมาย', 'times' => ['08:00', '10:00'], 'max' => 15],
            ['title' => 'ซ้อมเบาๆ ตอนเช้า', 'desc' => 'เริ่มวันใหม่ด้วยการออกกำลังกายเบาๆ เหมาะกับมือใหม่เน้นสุขภาพ', 'times' => ['06:00', '07:30'], 'max' => 10],
            ['title' => 'แมตช์กระชับมิตร', 'desc' => 'จัดแมตช์แข่งขันกันเองภายในกลุ่ม เน้นสนุกและได้เจอเพื่อนใหม่', 'times' => ['17:00', '19:00'], 'max' => 20],
            ['title' => 'กิจกรรมสายสุขภาพเข้มข้น', 'desc' => 'ออกกำลังกายแบบจัดเต็ม พร้อมแลกเปลี่ยนเคล็ดลับดูแลสุขภาพ', 'times' => ['18:30', '20:30'], 'max' => 12],
        ];

        $communities = Community::query()->orderBy('id')->get();

        foreach ($communities as $index => $community) {
            // 2) สมาชิกต่อคอมมู 3-7 คน (รวมเจ้าของ) กำหนดแบบคงที่จากลำดับ
            $target = 3 + ($index % 5);

            // ให้เจ้าของเป็นสมาชิก owner เสมอ (กันกรณีข้อมูลเก่าไม่มี)
            CommunityMember::firstOrCreate(
                ['community_id' => $community->id, 'user_id' => $community->created_by],
                ['role' => 'owner', 'status' => 'approved']
            );

            $approvedIds = CommunityMember::query()
                ->where('community_id', $community->id)
                ->where('status', 'approved')
                ->pluck('user_id')
                ->all();

            // เติมสมาชิกเดโม่จนครบจำนวนเป้าหมาย
            for ($offset = 0; $offset < count($demoUsers); $offset++) {
                if (count($approvedIds) >= $target) {
                    break;
                }
                $user = $demoUsers[($index + $offset) % count($demoUsers)];
                if (in_array($user->id, $approvedIds, true)) {
                    continue;
                }
                CommunityMember::firstOrCreate(
                    ['community_id' => $community->id, 'user_id' => $user->id],
                    ['role' => 'member', 'status' => 'approved']
                );
                $approvedIds[] = $user->id;
            }

            // กลุ่ม private เพิ่มคนที่รออนุมัติ 1 คน ไว้โชว์ปุ่มอนุมัติ/ปฏิเสธ
            if ($community->privacy === 'private') {
                $pendingIndex = (count($demoUsers) - 1 - ($index % 5));
                $pending = $demoUsers[$pendingIndex < 0 ? 0 : $pendingIndex];
                if (! in_array($pending->id, $approvedIds, true)) {
                    CommunityMember::firstOrCreate(
                        ['community_id' => $community->id, 'user_id' => $pending->id],
                        ['role' => 'member', 'status' => 'pending']
                    );
                }
            }

            // 3) กิจกรรมต่อคอมมู 1-3 อัน ไม่ใส่ภาพปก
            $activityCount = 1 + ($index % 3);
            $sportId = $community->sport_id ?: $sportIds[$index % count($sportIds)];

            for ($a = 0; $a < $activityCount; $a++) {
                $template = $activityTemplates[($index + $a) % count($activityTemplates)];

                $activity = Activity::firstOrCreate(
                    ['community_id' => $community->id, 'name' => $template['title'].' - '.$community->name],
                    [
                        'description' => $template['desc'],
                        'sport_id' => $sportId,
                        'location_id' => $locationIds[($index + $a) % count($locationIds)],
                        'skill_level' => 1 + (($index + $a) % 3),
                        'date' => now()->addDays(3 + ($a * 3) + ($index % 2))->toDateString(),
                        'start_time' => $template['times'][0],
                        'end_time' => $template['times'][1],
                        'max_participants' => $template['max'],
                        'created_by' => $community->created_by,
                    ]
                );

                // ให้สมาชิกบางส่วนลงชื่อเข้าร่วมกิจกรรม (ไม่เกินจำนวนสูงสุด)
                $participantLimit = min(2 + $a, count($approvedIds), $activity->max_participants);
                foreach (array_slice($approvedIds, 0, $participantLimit) as $userId) {
                    ActivityParticipant::firstOrCreate(
                        ['activity_id' => $activity->id, 'user_id' => $userId],
                        ['status' => 'registered']
                    );
                }
            }
        }
    }
}
