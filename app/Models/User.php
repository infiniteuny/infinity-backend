<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email_address',
        'phone_number',
        'student_id',
        'major_id',
        'links',
        'role',
        'start_date',
        'end_date',
        'is_member',
        'is_extraordinary',
    ];

    public function fundApplications()
    {
        return $this->hasMany(FundApplication::class, 'user_id', 'id');
    }

    public function testimonials()
    {
        return $this->hasMany(Testimonial::class, 'user_id', 'id');
    }
}
