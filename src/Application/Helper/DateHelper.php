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

use App\Application\Constant\DateFormatConstant;
use DateInterval;
use DatePeriod;
use DateTime;

class DateHelper
{
    public static function actualDay(): string|bool
    {
        return date(DateFormatConstant::DATABASE->value);
    }

    public static function actualWeekStartByDate(DateTime $date): DateTime
    {
        return $date->sub(new DateInterval('P' . ($date->format('w') - 1) . 'D'));
    }

    /**
     * @return false|string
     */
    public static function actualWeekStart(): string|bool
    {
        $time = time();
        $dateN = (int) date('n', $time);
        $dateJ = (int) date('j', $time);
        $dateY = (int) date('Y', $time);

        $weekStart = date('w') === '1' ?
            strtotime('0 hours 0 seconds') :
            strtotime('last Monday', mktime(0, 0, 0, $dateN, $dateJ, $dateY));

        return date(DateFormatConstant::DATABASE->value, $weekStart);
    }

    public static function actualWeekEnd(): string|bool
    {
        $weekStart = self::actualWeekStart();
        $weekEnd = $weekStart + 6 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE->value, $weekEnd);
    }

    public static function datePeriod(string $from, string $to, bool $mode = false): array
    {
        $from = new DateTime($from);
        $to = new DateTime($to);
        $to = $to->modify('+1 day');

        $period = new DatePeriod($from, new DateInterval('P1D'), $to);

        $arrayOfDates = array_map(static fn($item): string => $item->format(DateFormatConstant::DATE->value), iterator_to_array($period));

        if ($mode === true) {
            $arrayNameDay = [];
            foreach ($arrayOfDates as $date) {
                $arrayNameDay[] = (new DateTime($date))->format('D, d.m');
            }

            return $arrayNameDay;
        }

        return $arrayOfDates;
    }

    public static function nextWeek(string $week): string|bool
    {
        $nextWeek = strtotime($week);
        $nextWeek += 7 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE->value, $nextWeek);
    }

    public static function previousWeek(string $week): string|bool
    {
        $previousWeek = strtotime($week);
        $previousWeek -= 7 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE->value, $previousWeek);
    }

    public static function endWeek(string $week): string|bool
    {
        $endWeek = strtotime($week);
        $endWeek += 6 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE->value, $endWeek);
    }

    public static function changeFormatWeek(string $format, string $date): string|bool
    {
        $changeDate = strtotime($date);

        return date($format, $changeDate);
    }

    public static function checkWeek(string $date): string|bool
    {
        $current_time = strtotime($date);
        $actualWeek = date('d.m.Y', $current_time - (date('N', $current_time) - 1) * 86400);
        if ($actualWeek === $date) {
            return $date;
        }

        return $actualWeek;
    }

    public static function validateDate(string $format, string $date): bool
    {
        return date($format, strtotime($date)) === $date;
    }

    public static function firstDayMonth(): string|bool
    {
        return date(DateFormatConstant::DATABASE->value, strtotime('first day of this month'));
    }

    public static function lastDayMonth(): string|bool
    {
        return date(DateFormatConstant::DATABASE->value, strtotime('last day of this month'));
    }

    public static function plusDayDate(string $date, int $quantity): string|bool
    {
        return date(DateFormatConstant::DATABASE->value, strtotime($date . ' + ' . $quantity . ' days'));
    }

    public static function minusDayDate(string $date, int $quantity): string|bool
    {
        return date(DateFormatConstant::DATABASE->value, strtotime($date . ' - ' . $quantity . ' days'));
    }
}
