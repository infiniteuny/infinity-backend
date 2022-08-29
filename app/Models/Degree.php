<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;

class Degree extends Model
{
    use HasFactory, UUID;

    public function studyProgram()
    {
        return $this->hasMany(StudyProgram::class);
    }
}
