<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    protected $fillable = [
        'name',
        'description',
        'cover_image',
        'sport_id',
        'privacy',
        'created_by',
    ];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    // ===== เพิ่มใหม่ =====

    // สมาชิกที่อนุมัติแล้ว
    public function approvedMembers(): HasMany
    {
        return $this->hasMany(CommunityMember::class)->where('status', 'approved');
    }

    // คำขอที่รออนุมัติ
    public function pendingMembers(): HasMany
    {
        return $this->hasMany(CommunityMember::class)->where('status', 'pending');
    }

    // หาข้อมูลสมาชิกของ user คนนี้ในกลุ่มนี้ (ถ้าไม่เคยสมัครจะได้ null)
    public function membershipOf($user)
    {
        return CommunityMember::where('community_id', $this->id)
            ->where('user_id', $user->id)
            ->first();
    }

    // user คนนี้เป็นเจ้าของกลุ่มไหม
    public function isOwner($user)
    {
        $member = $this->membershipOf($user);

        if ($member == null) {
            return false; // ไม่เคยสมัคร = ไม่ใช่เจ้าของ
        }

        return $member->role == 'owner' && $member->status == 'approved';
    }

    // user คนนี้เป็นสมาชิกที่อนุมัติแล้วไหม (เจ้าของก็นับด้วย)
    public function isApprovedMember($user)
    {
        $member = $this->membershipOf($user);

        if ($member == null) {
            return false;
        }

        return $member->status == 'approved';
    }
}
