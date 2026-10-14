<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Community;
use App\Models\MatchWeight;
use App\Models\User;
use App\Models\UserAvailability;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class MatchingService
{
    // น้ำหนักเริ่มต้น ใช้เมื่อในตาราง match_weights ยังไม่มีแถวนั้น กันไว้ถ้ายังไม่ได้รัน seeder
    private const DEFAULT_WEIGHTS = [
        'sport_match' => 0.30,
        'goal_match' => 0.20,
        'skill_match' => 0.20,
        'time_overlap' => 0.15,
        'location_match' => 0.15,
        'community_sport' => 0.60,
        'community_activity' => 0.40,
    ];

    // คำนวณ % match ของกิจกรรมทุกอัน ให้ user ที่ล็อกอินอยู่
    /**
     * @return Collection<int, Activity>
     */
    public function getRecommendations(User $user): Collection
    {
        $profile = $user->profile;
        $mySportIds = $user->sports->pluck('id')->toArray();
        $weights = $this->getWeights();

        // ซ่อนเฉพาะกิจกรรมที่ user ยังลงทะเบียนอยู่ ซึ่งยกเลิกแล้วยังเห็น
        $activities = Activity::with(['sport', 'location', 'community'])
            ->withCount(['participants as registered_count' => fn ($q) => $q->where('status', 'registered')])
            ->whereDate('date', '>=', today())
            ->where(function ($q) use ($user) {
                // ไม่สังกัดกลุ่ม หรือกลุ่มไม่ใช่ private หรือเราเป็นสมาชิกที่อนุมัติแล้ว
                $q->whereNull('community_id')
                    ->orWhereHas('community', fn ($c) => $c->where('privacy', '!=', 'private'))
                    ->orWhereHas('community.approvedMembers', fn ($m) => $m->where('user_id', $user->id));
            })
            ->whereDoesntHave('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'registered');
            })
            ->get();

        foreach ($activities as $activity) {
            $activity->setAttribute('match_score', $this->scoreActivity($activity, $user, $profile, $mySportIds, $weights));
        }

        return $activities->sortByDesc('match_score')->values();
    }

    // คำนวณ % match ของ Community ที่ user ยังไม่ได้เข้าร่วม
    /**
     * @return Collection<int, Community>
     */
    public function getCommunityRecommendations(User $user): Collection
    {
        $profile = $user->profile;
        $mySportIds = $user->sports->pluck('id')->toArray();
        $weights = $this->getWeights();

        // คะแนนกิจกรรมของกลุ่ม = ค่าเฉลี่ยคะแนนของกิจกรรมที่ยังไม่จบทั้งหมดในกลุ่มนั้น
        // นับรวมกิจกรรมที่ user ลงทะเบียนแล้วด้วย เพราะวัดว่ากลุ่มนี้จัดกิจกรรมที่เหมาะกับ user แค่ไหน
        $groupActivities = Activity::with(['sport', 'location'])
            ->whereNotNull('community_id')
            ->whereDate('date', '>=', today())
            ->where(function ($q) use ($user) {
                $q->whereNull('community_id')
                    ->orWhereHas('community', fn ($c) => $c->where('privacy', '!=', 'private'))
                    ->orWhereHas('community.approvedMembers', fn ($m) => $m->where('user_id', $user->id));
            })
            ->get();

        $activityScores = [];
        foreach ($groupActivities->groupBy('community_id') as $communityId => $list) {
            $sum = 0;
            foreach ($list as $activity) {
                $sum += $this->scoreActivity($activity, $user, $profile, $mySportIds, $weights);
            }
            $activityScores[$communityId] = $sum / $list->count();
        }

        // ซ่อนกลุ่มที่เป็นสมาชิกอยู่ หรือส่งคำขอรออนุมัติอยู่
        $communities = Community::with('sport')
            ->withCount('approvedMembers')
            ->whereDoesntHave('members', function ($q) use ($user) {
                $q->where('user_id', $user->id)->whereIn('status', ['approved', 'pending']);
            })
            ->get();

        $sportWeight = $weights['community_sport'];
        $activityWeight = $weights['community_activity'];
        $totalWeight = $sportWeight + $activityWeight;

        foreach ($communities as $community) {
            // กีฬาของกลุ่มตรงกับที่ user สนใจมั้ย
            $sportPart = in_array($community->sport_id, $mySportIds) ? 1 : 0;

            // คะแนนเฉลี่ยของกิจกรรมในกลุ่ม ไม่มีกิจกรรม = 0.5
            $activityPart = 0.5;
            if (isset($activityScores[$community->id])) {
                $activityPart = $activityScores[$community->id] / 100;
            }

            $score = 0;
            if ($totalWeight > 0) {
                // หารด้วยผลรวมน้ำหนัก ถึงแอดมินตั้งรวมไม่เป็น 1 คะแนนก็ไม่เกิน 100
                $score = (($sportPart * $sportWeight) + ($activityPart * $activityWeight)) / $totalWeight;
            }

            $community->setAttribute('match_score', round($score * 100));
        }

        return $communities->sortByDesc('match_score')->values();
    }

    // อ่านน้ำหนักจากตาราง match_weights ตัวไหนไม่มีให้ใช้ค่าเริ่มต้นแทน
    /**
     * @return array<string, float>
     */
    private function getWeights(): array
    {
        $weights = self::DEFAULT_WEIGHTS;

        foreach (MatchWeight::pluck('weight_value', 'factor_name') as $name => $value) {
            $weights[$name] = (float) $value;
        }

        return $weights;
    }

    // คะแนน % ของกิจกรรม 1 อัน รวม 5 ปัจจัยตามน้ำหนัก
    /**
     * @param  array<int, mixed>  $mySportIds
     * @param  array<string, float>  $weights
     */
    private function scoreActivity(Activity $activity, User $user, ?UserProfile $profile, array $mySportIds, array $weights): float
    {
        $score = 0;
        $score += $this->sportScore($activity, $mySportIds) * $weights['sport_match'];
        $score += $this->goalScore($activity, $profile) * $weights['goal_match'];
        $score += $this->skillScore($activity, $profile) * $weights['skill_match'];
        $score += $this->timeScore($activity, $user) * $weights['time_overlap'];
        $score += $this->locationScore($activity, $profile) * $weights['location_match'];

        // หารด้วยผลรวมน้ำหนัก ถ้ารวมได้ 1.00 ผลจะเท่าเดิม
        $total = $weights['sport_match'] + $weights['goal_match'] + $weights['skill_match']
            + $weights['time_overlap'] + $weights['location_match'];

        if ($total <= 0) {
            return 0;
        }

        // แปลงเป็นเปอร์เซ็นต์ 0-100
        return round($score / $total * 100);
    }

    // 1) กีฬาตรงกับที่ user สนใจไหม ตรง = 1, ไม่ตรง = 0
    /**
     * @param  array<int, mixed>  $mySportIds
     */
    private function sportScore(Activity $activity, array $mySportIds): float
    {
        if (in_array($activity->sport_id, $mySportIds)) {
            return 1;
        }

        return 0;
    }

    // 2) เป้าหมาย goal เลือกจาก dropdown ในหน้าโปรไฟล์ ลดน้ำหนัก, เพิ่มกล้ามเนื้อ, สุขภาพทั่วไป บลาๆๆ
    private function goalScore(Activity $activity, ?UserProfile $profile): float
    {
        if (! $profile || ! $profile->goal) {
            return 0.5;
        }

        $goal = $profile->goal;
        $sportName = $activity->sport->name ?? '';

        if (str_contains($goal, 'ลดน้ำหนัก') && in_array($sportName, ['วิ่ง', 'ปั่นจักรยาน', 'ฟิตเนส'])) {
            return 1;
        }
        if (str_contains($goal, 'กล้าม') && $sportName == 'ฟิตเนส') {
            return 1;
        }
        if (str_contains($goal, 'สุขภาพ') && in_array($sportName, ['โยคะ', 'วิ่ง', 'แบดมินตัน', 'ฟุตบอล'])) {
            return 1;
        }

        return 0.5;
    }

    // 3) ระดับ skill_level ของ user เป็นข้อความ (เริ่มต้น/ปานกลาง/ผู้เชี่ยวชาญ) แต่ของกิจกรรมเป็นเลข 1-3 เลยต้องแปลงก่อน
    private function skillScore(Activity $activity, ?UserProfile $profile): float
    {
        if (! $profile || ! $profile->skill_level) {
            return 0.5;
        }

        $text = $profile->skill_level;

        if ($text == 'เริ่มต้น') {
            $level = 1;
        } elseif ($text == 'ปานกลาง') {
            $level = 2;
        } elseif ($text == 'ผู้เชี่ยวชาญ') {
            $level = 3;
        } else {
            return 0.5;
        }

        $diff = abs($level - $activity->skill_level);

        if ($diff == 0) {
            return 1;
        }
        if ($diff == 1) {
            return 0.5;
        }

        return 0;
    }

    // 4) เวลา เช็คว่าเวลาเริ่มกิจกรรมอยู่ในช่วงว่างของ user ไหม ถ้าไม่มีข้อมูลเลย = 0.5
    private function timeScore(Activity $activity, User $user): float
    {
        $slots = UserAvailability::where('user_id', $user->id)->get();

        if ($slots->isEmpty()) {
            return 0.5;
        }

        // วันในสัปดาห์ของกิจกรรม 0 = อาทิตย์ เหมือนที่เก็บใน day_of_week
        $day = Carbon::parse($activity->date)->dayOfWeek;
        // เอาแค่ HH:MM จาก start_time
        $start = substr((string) $activity->start_time, 0, 5);

        foreach ($slots as $slot) {
            if ($slot->day_of_week != $day) {
                continue;
            }

            // time_slot เก็บเป็นข้อความ เช่น "06:00-11:59"
            [$from, $to] = explode('-', $slot->time_slot);

            if ($start >= $from && $start <= $to) {
                return 1;
            }
        }

        return 0;
    }

    // 5) สถานที่ พื้นที่สะดวกในโปรไฟล์เก็บเป็นพิกัด lat,lng เลยเทียบระยะทางกับสถานที่จัดกิจกรรม
    private function locationScore(Activity $activity, ?UserProfile $profile): float
    {
        if (! $profile || ! $profile->preferred_location || ! $activity->location) {
            return 0.5;
        }

        $place = $profile->preferred_location;

        if (str_contains($place, ',')) {
            $parts = explode(',', $place);
            $km = $this->distanceKm(
                (float) $parts[0],
                (float) ($parts[1] ?? 0),
                (float) $activity->location->lat,
                (float) $activity->location->lng
            );

            // ไม่เกิน 3 กม. = 1, ไม่เกิน 8 กม. = 0.5, ไกลกว่านั้น = 0
            if ($km <= 3) {
                return 1;
            }
            if ($km <= 8) {
                return 0.5;
            }

            return 0;
        }

        // เผื่อข้อมูลเก่าที่เก็บเป็นข้อความ เช่น "มข" ให้เช็คแบบเดิม
        $name = $activity->location->name ?? '';
        $address = $activity->location->address ?? '';

        if (str_contains($name, $place) || str_contains($address, $place)) {
            return 1;
        }

        return 0;
    }

    // ระยะทางระหว่าง 2 พิกัด หน่วยกิโลเมตร จากสูตร Haversine
    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * asin(sqrt($a));
    }
}
