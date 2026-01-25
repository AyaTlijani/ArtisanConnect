<?php

namespace App\Type;

use App\Enum\UserRole;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class UserRoleType extends Type
{
    const USER_ROLE = 'user_role';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('ARTISAN', 'CLIENT')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        return UserRole::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        if (!$value instanceof UserRole) {
            throw new \InvalidArgumentException('Value must be an instance of UserRole');
        }
        return $value->value;
    }

    public function getName()
    {
        return self::USER_ROLE;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}