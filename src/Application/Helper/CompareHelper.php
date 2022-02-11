<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\Helper;

use App\Application\Constant\{
    CompareConstant,
    DateFormatConstant
};
use DateTime;

class CompareHelper
{
    public static function compare(
        mixed $value1,
        mixed $value2,
        string $operator
    ): bool {
        return match ($operator) {
            CompareConstant::LESS => $value1 < $value2,
            CompareConstant::LESS_EQUAL => $value1 <= $value2,
            CompareConstant::MORE => $value1 > $value2,
            CompareConstant::MORE_EQUAL => $value1 >= $value2,
            CompareConstant::EQUAL => $value1 === $value2,
            CompareConstant::NOT_EQUAL => $value1 != $value2,
            default => false,
        };
    }

    public static function compareDateTime(
        DateTime $first,
        DateTime $second,
        string $condition
    ): bool {
        return self::compare(
            strtotime($first->format(DateFormatConstant::DATABASE)),
            strtotime($second->format(DateFormatConstant::DATABASE)),
            $condition
        );
    }
}
