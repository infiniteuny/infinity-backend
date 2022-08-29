<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Traits\UUID;

class Role extends SpatieRole
{
    use UUID;
}
