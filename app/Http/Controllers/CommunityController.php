<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    public function index()
    {
        $communities = Community::with('sport')
            ->latest()
            ->get();

        return view('community.index', compact('communities'));
    }

    public function create()
    {
        $sports = Sport::orderBy('name')->get();

        return view('community.create', compact('sports'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sport_id' => ['nullable', 'exists:sports,id'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'privacy' => ['required', 'in:public,private'],
        ]);

        $validated['created_by'] = auth()->id();

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')
                ->store('communities', 'public');
        }

        $community = Community::create($validated);

        return redirect()
            ->route('community.show', $community)
            ->with('success', 'สร้าง Community สำเร็จ');
    }

    public function show(Community $community)
    {
        $community->load([
            'sport',
            'creator',
            'activities',
            'members.user',
            'posts.user',
        ]);

        return view('community.show', compact('community'));
    }
}