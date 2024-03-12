<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoreTeamDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority',
    ];

    protected $dateFormat = DATE_ATOM;

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function coreTeamMembers()
    {
        return $this->hasMany(CoreTeamMember::class);
    }
}
