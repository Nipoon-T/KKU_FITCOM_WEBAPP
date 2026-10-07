<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\MatchWeight;
use App\Models\User;
use App\Models\UserProfile;
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

        // เอาเฉพาะกิจกรรมที่ user ยังไม่ได้เข้าร่วม
        $activities = Activity::with(['sport', 'location'])
            ->whereDoesntHave('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get();

        foreach ($activities as $activity) {
            $score = 0;
            $score += $this->sportScore($activity, $mySportIds) * $weights['sport_match'];
            $score += $this->goalScore($activity, $profile) * $weights['goal_match'];
            $score += $this->skillScore($activity, $profile) * $weights['skill_match'];
            $score += $this->timeScore() * $weights['time_overlap'];
            $score += $this->locationScore($activity, $profile) * $weights['location_match'];

            // แปลงเป็นเปอร์เซ็นต์ (0-100) แล้วแปะไว้ที่กิจกรรม
            $activity->setAttribute('match_score', round($score * 100));
        }

        return $activities->sortByDesc('match_score')->values();
    }

    // 1) กีฬาตรงกับที่ user สนใจไหม (ตรง = 1, ไม่ตรง = 0)
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

    // 2) เป้าหมาย: goal เป็นข้อความที่ user พิมพ์เอง เลยเช็คว่ามีคำนี้อยู่ไหม
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

    // 3) ระดับ: skill_level ของ user เป็นข้อความ แต่ของกิจกรรมเป็นเลข 1-3 เลยต้องแปลงก่อน
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
        } elseif (str_contains($text, 'สูง')) {
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

    // 4) เวลา: ยังไม่มีข้อมูลเวลาว่างของ user ในฐานข้อมูล เลยให้ 0.5 ไปก่อน
    private function timeScore(): float
    {
        return 0.5;
    }

    // 5) สถานที่: ชื่อ/ที่อยู่สถานที่จัดกิจกรรม มีคำที่ user กรอกใน preferred_location ไหม
    private function locationScore(Activity $activity, ?UserProfile $profile): float
    {
        if (! $profile || ! $profile->preferred_location || ! $activity->location) {
            return 0.5;
        }

        $place = $profile->preferred_location;
        $name = $activity->location->name ?? '';
        $address = $activity->location->address ?? '';

        if (str_contains($name, $place) || str_contains($address, $place)) {
            return 1;
        }

        return 0;
    }
}
