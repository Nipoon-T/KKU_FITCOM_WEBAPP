<?php

namespace App\Models;

use Database\Factories\SportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    /** @use HasFactory<SportFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * @return BelongsToMany<User, Sport>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_sport_preferences');
    }

        /**
     * @return HasMany<Community, $this>
     */
    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }
}
