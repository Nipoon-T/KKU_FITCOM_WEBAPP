<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(): View
    {
        $badges = Badge::where('condition_type', 'manual')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('admin.badges.index', compact('badges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:badges,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:10'],
        ], [
            'name.required' => 'กรุณาใส่ชื่อเหรียญ',
            'name.unique' => 'มีเหรียญชื่อนี้แล้ว',
            'icon.required' => 'กรุณาใส่ไอคอน (emoji)',
        ]);

        Badge::create($data + ['condition_type' => 'manual', 'condition_value' => 0]);

        return back()->with('status', 'สร้างเหรียญ "'.$data['name'].'" แล้ว');
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        // ลบได้เฉพาะเหรียญพิเศษ
        abort_if($badge->condition_type !== 'manual', 403);

        $badge->delete();

        return back()->with('status', 'ลบเหรียญ "'.$badge->name.'" แล้ว');
    }
}
