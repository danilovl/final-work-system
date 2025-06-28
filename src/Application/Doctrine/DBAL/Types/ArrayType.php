<?php declare(strict_types=1);

namespace App\Application\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\{
    Type,
    ConversionException
};
use Doctrine\Deprecations\Deprecation;
use function is_resource;
use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function stream_get_contents;
use function unserialize;
use const E_DEPRECATED;
use const E_USER_DEPRECATED;

/**
 * Type that maps a PHP array to a clob SQL type.
 *
 * @deprecated Use {@link JsonType} instead.
 */
class ArrayType extends Type
{
    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        // @todo 3.0 - $value === null check to save real NULL in database
        return serialize($value);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        set_error_handler(static function (int $code, string $message): bool {
            if ($code === E_DEPRECATED || $code === E_USER_DEPRECATED) {
                return false;
            }

            throw new ConversionException($message);
        });

        try {
            return unserialize($value);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'array';
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        Deprecation::triggerIfCalledFromOutside(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/pull/5509',
            '%s is deprecated.',
            __METHOD__,
        );

        return true;
    }
}
