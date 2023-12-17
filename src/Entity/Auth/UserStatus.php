<?php

namespace App\Entity\Auth;

enum UserStatus: string
{
    case Active = "active";
    case Blocked = "blocked";
    case Banned = "banned";
}
