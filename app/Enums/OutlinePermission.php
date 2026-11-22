<?php

namespace App\Enums;

enum OutlinePermission: string
{
    case READ = 'read';
    case READ_WRITE = 'read_write';
}
