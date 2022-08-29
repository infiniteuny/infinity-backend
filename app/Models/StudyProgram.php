<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;

class StudyProgram extends Model
{
    use HasFactory, UUID;

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function degree()
    {
        return $this->belongsTo(Degree::class);
    }
}
