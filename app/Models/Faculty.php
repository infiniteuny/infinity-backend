<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    // protected $dateFormat = DATE_ATOM;

    // protected function serializeDate(DateTimeInterface $date): string
    // {
    //     return $date->format(DATE_ATOM);
    // }

    public function majors()
    {
        return $this->hasMany(Major::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Major::class, 'faculty_id', 'major_id', 'id', 'id');
    }
}
