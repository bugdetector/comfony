<?php

namespace App\Entity\File;

enum FileStatus: string
{
    case Temporary = 'Temporary';
    case Permanent = 'Permanent';
}
