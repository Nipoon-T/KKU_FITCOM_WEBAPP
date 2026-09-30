<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $sports = Sport::all();
        $selectedAvailability = $user->availabilities->map(fn ($a) => $a->day_of_week.'_'.$a->time_slot)->toArray();

        return view('profile.setup', [
            'user' => $user,
            'sports' => $sports,
            'selectedAvailability' => $selectedAvailability,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'goal' => 'nullable|string',
            'skill_level' => 'nullable|string',
            'preferred_location' => 'nullable|string',
            'bio' => 'nullable|string',
            'sports' => 'nullable|array',
            'sports.*' => 'exists:sports,id',
            'availability' => 'nullable|array',
        ]);

        $user = auth()->user();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['goal', 'skill_level', 'preferred_location', 'bio'])
        );

        $user->sports()->sync($request->input('sports', []));

        $user->availabilities()->delete();
        foreach ($request->input('availability', []) as $slot) {
            [$day, $timeSlot] = explode('_', $slot);
            $user->availabilities()->create([
                'day_of_week' => $day,
                'time_slot' => $timeSlot,
            ]);
        }

        return redirect()->route('profile.setup')->with('status', 'บันทึกโปรไฟล์สำเร็จ');
    }
}
