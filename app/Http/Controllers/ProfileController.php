<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sport;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $sports = Sport::all();

        return view('profile.setup', [
            'user' => $user,
            'sports' => $sports,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'goal' => 'nullable|string',
            'skill_level' => 'nullable|string',
            'preferred_location' => 'nullable|string',
            'bio' => 'nullable|string',
        ]);

        $user = auth()->user();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['goal', 'skill_level', 'preferred_location', 'bio'])
        );

        return redirect()->route('profile.setup')->with('status', 'บันทึกโปรไฟล์สำเร็จ');
    }
}