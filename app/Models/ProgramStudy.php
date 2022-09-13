<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramStudy extends Model
{
    use HasFactory;

    public function members()
    {
        return $this->hasMany(Member::class, 'program_study_id', 'id');
    }

    public function grades()
    {
        return $this->belongsTo(Grade::class);
    }

    public function faculties()
    {
        return $this->belongsTo(Faculty::class);
    }
}
