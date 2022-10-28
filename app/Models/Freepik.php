<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Freepik extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'file_name',
        'file_path',
        'file_size',
        'status',
    ];

    public function freepikDownloads()
    {
        return $this->belongsTo(FreepikDownload::class, 'freepik_download_id', 'id');
    }
}
