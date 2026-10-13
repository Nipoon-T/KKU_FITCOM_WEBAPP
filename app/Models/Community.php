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
        'cover_position',
        'sport_id',
        'privacy',
        'created_by',
    ];

    /**
     * @return BelongsTo<Sport, $this>
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * @return HasMany<CommunityMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    /**
     * @return HasMany<CommunityPost, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    // สมาชิกที่อนุมัติแล้ว

    /**
     * @return HasMany<CommunityMember, $this>
     */
    public function approvedMembers(): HasMany
    {
        return $this->hasMany(CommunityMember::class)->where('status', 'approved');
    }

    // คำขอที่รออนุมัติ

    /**
     * @return HasMany<CommunityMember, $this>
     */
    public function pendingMembers(): HasMany
    {
        return $this->hasMany(CommunityMember::class)->where('status', 'pending');
    }

    // หาข้อมูลสมาชิกของ user คนนี้ในกลุ่มนี้ (ถ้าไม่เคยสมัครจะได้ null)
    // ?User = รับ User หรือ null ก็ได้ / ?CommunityMember = คืนค่า CommunityMember หรือ null
    public function membershipOf(?User $user): ?CommunityMember
    {
        if ($user === null) {
            return null; // ยังไม่ล็อกอิน = ไม่ใช่สมาชิก
        }

        return CommunityMember::where('community_id', $this->id)
            ->where('user_id', $user->id)
            ->first();
    }

    // user คนนี้เป็นเจ้าของกลุ่มไหม (คืนค่า true / false)
    public function isOwner(?User $user): bool
    {
        $member = $this->membershipOf($user);

        if ($member === null) {
            return false; // ไม่เคยสมัคร = ไม่ใช่เจ้าของ
        }

        return $member->role === 'owner' && $member->status === 'approved';
    }

    // user คนนี้เป็นสมาชิกที่อนุมัติแล้วไหม (เจ้าของก็นับด้วย)
    public function isApprovedMember(?User $user): bool
    {
        $member = $this->membershipOf($user);

        if ($member === null) {
            return false;
        }

        return $member->status === 'approved';
    }
}
