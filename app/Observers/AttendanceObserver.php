<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Services\GamificationService;

class AttendanceObserver
{
    public function __construct(private GamificationService $gamification) {}

    public function created(Attendance $attendance): void
    {
        $this->gamification->awardForAttendance($attendance);
    }
}
