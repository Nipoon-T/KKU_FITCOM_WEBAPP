<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['name' => 'First Step', 'description' => 'ก้าวแรกสู่สนาม ลุกจากเตียงมาได้ก็เก่งแล้วไปกันต่อ', 'icon' => '🐣', 'condition_type' => 'total_activities', 'condition_value' => 1],
            ['name' => 'Rising Star', 'description' => 'มาครบ 10 ครั้ง เก่งที่สุด', 'icon' => '💪', 'condition_type' => 'total_activities', 'condition_value' => 10],
            ['name' => 'Fit Star', 'description' => 'ครบ 30 ครั้ง ระดับตำนาน ไม่มีใครต้าน', 'icon' => '🏆', 'condition_type' => 'total_activities', 'condition_value' => 30],
            ['name' => 'Fit Streak', 'description' => 'ต่อเนื่อง 3 วัน เริ่มติดแล้วสิ', 'icon' => '✨', 'condition_type' => 'streak_days', 'condition_value' => 3],
            ['name' => 'Unstoppable', 'description' => 'ต่อเนื่อง 7 วัน ไม่มีเบรก', 'icon' => '🔥', 'condition_type' => 'streak_days', 'condition_value' => 7],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                [
                    'condition_type' => $badge['condition_type'],
                    'condition_value' => $badge['condition_value'],
                ],
                $badge
            );
        }
    }
}
