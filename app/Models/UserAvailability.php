<?php

namespace App\Models;

use Database\Factories\UserAvailabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAvailability extends Model
{
    /** @use HasFactory<UserAvailabilityFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'day_of_week', 'time_slot'];

    /**
     * @return BelongsTo<User, UserAvailability>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
