<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'url',
        'organizer',
        'organizer_type_id',
        'logo',
    ];

    protected $dateFormat = DATE_ATOM;

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }
}
