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

namespace App\Helper;

use App\Constant\{
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
        switch ($operator) {
            case CompareConstant::LESS:
                return $value1 < $value2;
            case CompareConstant::LESS_EQUAL:
                return $value1 <= $value2;
            case CompareConstant::MORE:
                return $value1 > $value2;
            case  CompareConstant::MORE_EQUAL:
                return $value1 >= $value2;
            case CompareConstant::EQUAL:
                return $value1 === $value2;
            case CompareConstant::NOT_EQUAL:
                return $value1 != $value2;
            default:
                return false;
        }
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
 