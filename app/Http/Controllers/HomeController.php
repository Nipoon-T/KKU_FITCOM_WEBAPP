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
        $communities = Community::latest()->take(6)->get();
        $activities = Activity::where('date', '>=', now())->orderBy('date')->take(6)->get();

        return view('home', [
            'sports' => $sports,
            'communities' => $communities,
            'activities' => $activities,
        ]);
    }
}
