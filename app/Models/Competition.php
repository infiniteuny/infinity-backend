<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'url',
        'organizer',
        'organizer_type_id',
        'logo',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function competitionOrganizerType()
    {
        return $this->belongsTo(CompetitionOrganizerType::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }
}
