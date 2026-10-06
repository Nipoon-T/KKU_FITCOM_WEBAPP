<?php

namespace Database\Seeders;

use App\Models\MatchWeight;
use Illuminate\Database\Seeder;

class MatchWeightSeeder extends Seeder
{
    public function run(): void
    {
        // น้ำหนักรวมกันต้องได้ 1.00
        $weights = [
            'sport_match'    => 0.30,
            'goal_match'     => 0.20,
            'skill_match'    => 0.20,
            'time_overlap'   => 0.15,
            'location_match' => 0.15,
        ];

        foreach ($weights as $name => $value) {
            // ถ้ามีอยู่แล้วให้อัปเดต ไม่สร้างซ้ำ
            MatchWeight::updateOrCreate(
                ['factor_name' => $name],
                ['weight_value' => $value]
            );
        }
    }
}
