<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'competition_id',
        'competition_team_type_id',
        'competition_scale_id',
        'competition_branch',
        'competition_date',
        'letter_of_acceptance',
        'proposal',
        'status'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
