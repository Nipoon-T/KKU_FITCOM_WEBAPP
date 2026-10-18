<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CommunityDemoAllSeeder extends Seeder
{
    // รันข้อมูลเดโม่ Community ทั้งหมดตามลำดับ (รันซ้ำได้)
    // รันด้วย: php artisan db:seed --class=CommunityDemoAllSeeder
    public function run(): void
    {
        $this->call([
            SportSeeder::class,
            LocationSeeder::class,
            BadgeSeeder::class,
            MatchWeightSeeder::class,
            CommunityDemoBaseSeeder::class,
            CommunityDemoSeeder::class,
            CommunityDemoPointsSeeder::class,
        ]);
    }
}
