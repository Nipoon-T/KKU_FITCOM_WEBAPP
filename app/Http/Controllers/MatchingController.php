<?php

namespace App\Http\Controllers;

use App\Services\MatchingService;

class MatchingController extends Controller
{
    // หน้า "แนะนำสำหรับคุณ"
    public function index(MatchingService $service)
    {
        $recommendations = $service->getRecommendations(auth()->user());

        return view('recommendations.index', compact('recommendations'));
    }
}
