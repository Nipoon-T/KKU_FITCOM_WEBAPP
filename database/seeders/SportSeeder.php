<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['วิ่ง', 'ปั่นจักรยาน', 'ฟุตบอล', 'แบดมินตัน', 'ฟิตเนส', 'โยคะ'] as $name) {
            Sport::firstOrCreate(['name' => $name]);
        }
    }
}
