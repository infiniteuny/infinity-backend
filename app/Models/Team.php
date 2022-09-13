<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    public function members()
    {
        return $this->belongsToMany(Member::class)->withPivot('role');
    }

    public function achievements()
    {
        return $this->hasOne(Achievement::class);
    }
}
