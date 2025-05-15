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
use DateTimeImmutable;

class DateHelper
{
    public static function actualDay(): string
    {
        return date(DateFormatConstant::DATABASE->value);
    }

    public static function actualWeekStartByDate(DateTime $date): DateTime
    {
        $number = $date->format('w');
        if ($number == 0) {
            $number = 7;
        }

        if ($number != 0) {
            $number -= 1;
        }

        return $date->sub(new DateInterval('P' . $number . 'D'));
    }

    public static function actualWeekStart(): string
    {
        $time = time();
        $dateN = (int) date('n', $time);
        $dateJ = (int) date('j', $time);
        $dateY = (int) date('Y', $time);

        /** @var int $baseTimestamp */
        $baseTimestamp = mktime(0, 0, 0, $dateN, $dateJ, $dateY);

        $weekStart = date('w') === '1' ?
            strtotime('0 hours 0 seconds') :
            strtotime('last Monday', $baseTimestamp);

        return date(DateFormatConstant::DATABASE->value, $weekStart);
    }

    public static function actualWeekEnd(): string
    {
        $weekStart = self::actualWeekStart();
        /** @var int $weekEnd */
        $weekEnd = strtotime($weekStart . ' + 1 week');

        return date(DateFormatConstant::DATABASE->value, $weekEnd);
    }

    public static function datePeriod(string $from, string $to, bool $mode = false): array
    {
        $from = new DateTimeImmutable($from);
        $to = new DateTimeImmutable($to);
        $to = $to->modify('+1 day');

        $period = new DatePeriod($from, new DateInterval('P1D'), $to);

        $arrayOfDates = array_map(static fn ($item): string => $item->format(DateFormatConstant::DATE->value), iterator_to_array($period));

        if ($mode === true) {
            $arrayNameDay = [];
            foreach ($arrayOfDates as $date) {
                $arrayNameDay[] = (new DateTimeImmutable($date))->format('D, d.m');
            }

            return $arrayNameDay;
        }

        return $arrayOfDates;
    }

    public static function nextWeek(string $week): string
    {
        $nextWeek = strtotime($week);
        $nextWeek += 7 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE->value, $nextWeek);
    }

    public static function previousWeek(string $week): string
    {
        $previousWeek = strtotime($week);
        $previousWeek -= 7 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE->value, $previousWeek);
    }

    public static function endWeek(string $week): string
    {
        $endWeek = strtotime($week);
        $endWeek += 6 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE->value, $endWeek);
    }

    public static function changeFormatWeek(string $format, string $date): string
    {
        /** @var int $changeDate */
        $changeDate = strtotime($date);

        return date($format, $changeDate);
    }

    public static function checkWeek(string $date): string
    {
        /** @var int $currentTime */
        $currentTime = strtotime($date);
        $actualWeek = date(DateFormatConstant::DATE->value, $currentTime - (date('N', $currentTime) - 1) * 86_400);
        if ($actualWeek === $date) {
            return $date;
        }

        return $actualWeek;
    }

    public static function validateDate(string $format, string $date): bool
    {
        /** @var int $time */
        $time = strtotime($date);

        return date($format, $time) === $date;
    }

    public static function firstDayMonth(): string
    {
        /** @var int $time */
        $time = strtotime('first day of this month');

        return date(DateFormatConstant::DATABASE->value, $time);
    }

    public static function lastDayMonth(): string
    {
        /** @var int $time */
        $time = strtotime('last day of this month');

        return date(DateFormatConstant::DATABASE->value, $time);
    }

    public static function plusDayDate(string $date, int $quantity): string
    {
        /** @var int $time */
        $time = strtotime($date . ' + ' . $quantity . ' days');

        return date(DateFormatConstant::DATABASE->value, $time);
    }

    public static function minusDayDate(string $date, int $quantity): string
    {
        /** @var int $time */
        $time = strtotime($date . ' - ' . $quantity . ' days');

        return date(DateFormatConstant::DATABASE->value, $time);
    }
}
