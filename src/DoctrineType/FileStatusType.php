<?php

namespace App\DoctrineType;

use App\Entity\File\FileStatus;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.doctrine_enum_type')]
class FileStatusType extends AbstractEnumType
{
    public const NAME = 'fileStatusType';

    public static function getEnumsClass(): string
    {
        return FileStatus::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
