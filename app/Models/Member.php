<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    public function programStudies()
    {
        return $this->belongsTo(ProgramStudy::class, 'program_study_id', 'id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)->withPivot('role');
    }
}
