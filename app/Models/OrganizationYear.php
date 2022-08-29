<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;

class OrganizationYear extends Model
{
    use HasFactory, UUID;

    public function organization()
    {
        return $this->hasMany(Organization::class);
    }
}
