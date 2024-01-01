<?php

namespace App\Entity\Auth;

enum UserStatus: string
{
    case Active = 'Active';
    case Blocked = 'Blocked';
    case Banned = 'Banned';
}
