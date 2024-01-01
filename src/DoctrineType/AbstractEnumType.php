<?php

namespace App\DoctrineType;

use BackedEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use LogicException;

abstract class AbstractEnumType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $cases = array_column($this->getEnumsClass()::cases(), "value");
        return "ENUM('" . implode("','", $cases) . "')";
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        return null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (false === enum_exists($this::getEnumsClass(), true)) {
            throw new LogicException("This class should be an enum");
        }
        // 🔥 https://www.php.net/manual/en/backedenum.tryfrom.php
        return $this::getEnumsClass()::tryFrom($value);
    }

    abstract public static function getEnumsClass(): string;
}
