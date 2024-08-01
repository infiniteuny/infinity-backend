<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'leader_id',
        'is_personal',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function fundApplications()
    {
        return $this->hasMany(FundApplication::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }
}
