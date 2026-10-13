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
    // คำนวณ % match ของกิจกรรมทุกอัน ให้ user ที่ล็อกอินอยู่
    /**
     * @return Collection<int, Activity>
     */
    public function getRecommendations(User $user): Collection
    {
        $profile = $user->profile;
        $mySportIds = $user->sports->pluck('id')->toArray();
        $weights = MatchWeight::pluck('weight_value', 'factor_name');

        // ซ่อนเฉพาะกิจกรรมที่ user ยังลงทะเบียนอยู่ ซึ่งยกเลิกแล้วยังเห็น
        $activities = Activity::with(['sport', 'location', 'community'])
            ->withCount(['participants as registered_count' => fn ($q) => $q->where('status', 'registered')])
            ->whereDate('date', '>=', today())
            ->whereDoesntHave('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'registered');
            })
            ->get();

        foreach ($activities as $activity) {
            $score = 0;
            $score += $this->sportScore($activity, $mySportIds) * $weights['sport_match'];
            $score += $this->goalScore($activity, $profile) * $weights['goal_match'];
            $score += $this->skillScore($activity, $profile) * $weights['skill_match'];
            $score += $this->timeScore($activity, $user) * $weights['time_overlap'];
            $score += $this->locationScore($activity, $profile) * $weights['location_match'];

            // แปลงเป็นเปอร์เซ็นต์ 0-100 แล้วแปะไว้ที่กิจกรรม
            $activity->setAttribute('match_score', round($score * 100));
        }

        return $activities->sortByDesc('match_score')->values();
    }

    // คำนวณ % match ของ Community ที่ user ยังไม่ได้เข้าร่วม
    /**
     * @return Collection<int, Community>
     */
    public function getCommunityRecommendations(User $user): Collection
    {
        $mySportIds = $user->sports->pluck('id')->toArray();
        $weights = MatchWeight::pluck('weight_value', 'factor_name');

        // เอาคะแนนกิจกรรมที่คำนวณไว้แล้ว มาเฉลี่ยแยกตามกลุ่ม
        $activityScores = [];
        foreach ($this->getRecommendations($user)->groupBy('community_id') as $communityId => $list) {
            $activityScores[$communityId] = $list->avg('match_score');
        }

        // ซ่อนกลุ่มที่เป็นสมาชิกอยู่ หรือส่งคำขอรออนุมัติอยู่
        $communities = Community::with('sport')
            ->withCount('approvedMembers')
            ->whereDoesntHave('members', function ($q) use ($user) {
                $q->where('user_id', $user->id)->whereIn('status', ['approved', 'pending']);
            })
            ->get();

        foreach ($communities as $community) {
            // กีฬาของกลุ่มตรงกับที่ user สนใจไหม
            $sportPart = in_array($community->sport_id, $mySportIds) ? 1 : 0;

            // คะแนนเฉลี่ยของกิจกรรมในกลุ่ม (ไม่มีกิจกรรม = 0.5)
            $activityPart = 0.5;
            if (isset($activityScores[$community->id])) {
                $activityPart = $activityScores[$community->id] / 100;
            }

            // น้ำหนักอ่านจากตาราง match_weights (ถ้ายังไม่มีใช้ค่า 0.6 / 0.4)
            $sportWeight = $weights['community_sport'] ?? 0.6;
            $activityWeight = $weights['community_activity'] ?? 0.4;

            $score = ($sportPart * $sportWeight) + ($activityPart * $activityWeight);
            $community->setAttribute('match_score', round($score * 100));
        }

        return $communities->sortByDesc('match_score')->values();
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

    // 2) เป้าหมาย goal เลือกจาก dropdown ในหน้าโปรไฟล์ (ลดน้ำหนัก, เพิ่มกล้ามเนื้อ, สุขภาพทั่วไป, ...)
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

        if (str_contains($text, 'เริ่ม') || str_contains($text, 'มือใหม่')) {
            $level = 1;
        } elseif (str_contains($text, 'กลาง')) {
            $level = 2;
        } elseif (str_contains($text, 'สูง') || str_contains($text, 'เชี่ยวชาญ')) {
            $level = 3;
        } elseif (is_numeric($text)) {
            $level = (int) $text;
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

    // 5) สถานที่ พื้นที่สะดวกในโปรไฟล์เก็บเป็นพิกัด "lat,lng" เลยเทียบระยะทางกับสถานที่จัดกิจกรรม
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

    // ระยะทางระหว่าง 2 พิกัด หน่วยกิโลเมตร (สูตร Haversine)
    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * asin(sqrt($a));
    }
}
