<?php

namespace App\Enums;

enum OutlineUserRole: string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case VIEWER = 'viewer';
    case GUEST = 'guest';
}
