<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreepikDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'limit',
        'limit_addons',
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function freepiks()
    {
        return $this->hasMany(Freepik::class, 'freepik_download_id', 'id');
    }
}
