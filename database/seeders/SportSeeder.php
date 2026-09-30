<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    public function run(): void
    {
        $sports = ['วิ่ง', 'ปั่นจักรยาน', 'ฟุตบอล', 'แบดมินตัน', 'ฟิตเนส', 'โยคะ'];

        foreach ($sports as $sport) {
            Sport::create(['name' => $sport]);
        }
    }
}