<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'address', 'lat', 'lng'];

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}