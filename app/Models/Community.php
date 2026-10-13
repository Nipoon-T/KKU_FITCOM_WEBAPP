<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    // คอลัมน์ที่อนุญาตให้บันทึกผ่าน Community::create() / update()
    protected $fillable = [
        'name',
        'description',
        'cover_image',
        'sport_id',
        'privacy',
        'created_by',
    ];


    // กลุ่มนี้เป็นกีฬาอะไร (sport_id → sports.id)
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    // ใครเป็นคนสร้างกลุ่ม (created_by → users.id)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // กิจกรรมที่จัดภายใต้กลุ่มนี้ (ตารางของคนที่ 3)
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    // สมาชิกทุกสถานะ (approved + pending)
    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    // โพสต์ทั้งหมดในกลุ่ม
    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }
}
