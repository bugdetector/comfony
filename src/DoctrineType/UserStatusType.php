<?php

namespace App\DoctrineType;

use App\Entity\Auth\UserStatus;
use Doctrine\ORM\Mapping\InheritanceType;

#[InheritanceType()]
class UserStatusType extends AbstractEnumType
{
    public const NAME = 'userStatusType';

    public static function getEnumsClass(): string
    {
        return UserStatus::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
