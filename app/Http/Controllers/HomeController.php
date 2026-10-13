<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Community;
use App\Models\Sport;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $sports = Sport::all();

        // โหลดกีฬามาพร้อมกัน จะได้ไม่ query ซ้ำทีละการ์ด
        $communities = Community::with('sport')->latest()->take(6)->get();

        // whereDate เทียบแค่วันที่ กิจกรรมของวันนี้จึงยังแสดงอยู่
        $activities = Activity::with('sport')
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->take(6)
            ->get();

        return view('home', [
            'sports' => $sports,
            'communities' => $communities,
            'activities' => $activities,
        ]);
    }
}
