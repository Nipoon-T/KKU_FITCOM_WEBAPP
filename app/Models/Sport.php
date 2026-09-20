<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sport extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sport_preferences');
    }
}