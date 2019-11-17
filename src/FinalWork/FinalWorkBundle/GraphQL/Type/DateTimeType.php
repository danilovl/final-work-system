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

namespace FinalWork\FinalWorkBundle\GraphQL\Type;

use DateTimeImmutable;
use DateTimeInterface;
use FinalWork\FinalWorkBundle\Constant\DateTimeConstant;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;

class DateTimeType extends ScalarType
{
    /**
     * @var string
     */
    public $name = 'DateTime';

    /**
     * @var string
     */
    public $description = 'The `DateTime` scalar type represents time data';

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value): string
    {
        if (!$value instanceof DateTimeInterface) {
            throw new InvariantViolation('DateTime is not an instance of DateTimeInterface: ' . Utils::printSafe($value));
        }

        return $value->format(DateTimeConstant::MYSQL);
    }

    /**
     * @param mixed $value
     * @return DateTimeInterface|null
     */
    public function parseValue($value): ?DateTimeInterface
    {
        return DateTimeImmutable::createFromFormat(DateTimeConstant::MYSQL, $value) ?: null;
    }

    /**
     * @param $valueNode
     * @param array|null $variables
     * @return string|null
     */
    public function parseLiteral($valueNode, ?array $variables = null): ?string
    {
        if ($valueNode instanceof StringValueNode) {
            return $valueNode->value;
        }

        return null;
    }
}