<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'competition_name',
        'competition_url',
        'competition_date',
        'competition_branch',
        'team_name',
        'team_leader',
        'team_members',
        'student_id_card',
        'letter_of_acceptance',
        'budget_plan',
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
