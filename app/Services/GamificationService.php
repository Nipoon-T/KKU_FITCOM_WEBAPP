<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Badge;
use App\Models\PointLog;
use App\Models\Streak;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public const POINTS_PER_ACTIVITY = 10;

    /**
     * เรียกเมื่อมีการเช็คชื่อ: ให้แต้ม → อัปเดต streak → เช็คเหรียญ
     */
    public function awardForAttendance(Attendance $attendance): void
    {
        $participant = $attendance->participant;
        $user = $participant?->user;

        if ($participant === null || $user === null) {
            return;
        }

        $checkedInAt = CarbonImmutable::parse($attendance->checked_in_at);

        DB::transaction(function () use ($participant, $user, $checkedInAt): void {
            PointLog::create([
                'user_id' => $user->id,
                'activity_id' => $participant->activity_id,
                'points_change' => self::POINTS_PER_ACTIVITY,
                'reason' => 'เข้าร่วมกิจกรรมสำเร็จ',
                'created_at' => $checkedInAt,
            ]);

            $this->updateStreak($user, $checkedInAt);
            $this->checkBadges($user, $checkedInAt);
        });
    }

    private function updateStreak(User $user, CarbonInterface $checkedInAt): void
    {
        $streak = Streak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0],
        );

        $today = $checkedInAt->startOfDay();
        $last = $streak->last_activity_date;

        // เช็คชื่อวันเดียวกันซ้ำ หรือเช็คชื่อย้อนหลัง → ไม่เปลี่ยน streak
        if ($last !== null && $today->lessThanOrEqualTo($last)) {
            return;
        }

        if ($last !== null && $last->addDay()->isSameDay($today)) {
            $streak->current_streak++;       // ต่อจากเมื่อวาน
        } else {
            $streak->current_streak = 1;     // ครั้งแรก หรือขาดไปแล้ว
        }

        $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        $streak->last_activity_date = $today;
        $streak->save();
    }

    private function checkBadges(User $user, CarbonInterface $earnedAt): void
    {
        $totalActivities = Attendance::whereHas(
            'participant',
            fn ($q) => $q->where('user_id', $user->id)
        )->count();

        $currentStreak = (int) ($user->streak()->value('current_streak') ?? 0);

        $ownedIds = $user->badges()->pluck('badges.id');
        $notOwned = Badge::whereNotIn('id', $ownedIds)->get();

        foreach ($notOwned as $badge) {
            $value = match ($badge->condition_type) {
                'total_activities' => $totalActivities,
                'streak_days' => $currentStreak,
                default => null,
            };

            if ($value !== null && $value >= $badge->condition_value) {
                $user->badges()->attach($badge->id, ['earned_at' => $earnedAt]);
            }
        }
    }
}
