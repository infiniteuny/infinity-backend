<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    public function programStudies()
    {
        return $this->hasMany(ProgramStudy::class, 'faculty_id', 'id');
    }

    public function members()
    {
        return $this->hasManyThrough(Member::class, ProgramStudy::class, 'faculty_id', 'program_study_id', 'id', 'id');
    }
}
