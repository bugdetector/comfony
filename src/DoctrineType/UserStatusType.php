<?php

namespace App\DoctrineType;

use App\Entity\Auth\UserStatus;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.doctrine_enum_type')]
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
