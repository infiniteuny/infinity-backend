<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionScale extends Model
{
    use HasFactory;

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }
}
