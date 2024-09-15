<?php

namespace App\Models;

use App\Traits\HasBulkCreate;
use App\Traits\HasUuids;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionTeamType extends Model
{
    use HasBulkCreate, HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'weight',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
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
