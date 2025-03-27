<?php

namespace App\Facades;

use App\Repositories\StorageRepository;
use Illuminate\Support\Facades\Facade;

class Storage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StorageRepository::class;
    }
}
