<?php declare(strict_types=1);
/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\GraphQL\Type;

use App\Application\Constant\DateFormatConstant;
use DateTimeImmutable;
use DateTimeInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;

class DateTimeType extends ScalarType
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->name = 'DateTime';
        $this->description = 'The `DateTime` scalar type represents time data';
    }

    public function serialize($value): string
    {
        if (!$value instanceof DateTimeInterface) {
            throw new InvariantViolation('DateTime is not an instance of DateTimeInterface: ' . Utils::printSafe($value));
        }

        return $value->format(DateFormatConstant::DATABASE->value);
    }

    /**
     * @param string $value
     */
    public function parseValue($value): ?DateTimeInterface
    {
        return DateTimeImmutable::createFromFormat(DateFormatConstant::DATABASE->value, $value) ?: null;
    }

    public function parseLiteral($valueNode, ?array $variables = null): ?string
    {
        if ($valueNode instanceof StringValueNode) {
            return $valueNode->value;
        }

        return null;
    }
}
