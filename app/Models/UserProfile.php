<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'goal', 'skill_level', 'preferred_location', 'bio', 'avatar'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
