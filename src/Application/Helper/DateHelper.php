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
        return (new DateTime)->format(DateFormatConstant::DATABASE->value);
    }

    public static function actualWeekStartByDate(DateTime $date): DateTime
    {
        return (clone $date)->modify('this week monday');
    }

    public static function actualWeekStart(): string
    {
        return (new DateTime)
            ->modify('this week monday')
            ->format(DateFormatConstant::DATABASE->value);
    }

    public static function actualWeekEnd(): string
    {
        return (new DateTime)
            ->modify('this week sunday')
            ->format(DateFormatConstant::DATABASE->value);
    }

    public static function datePeriod(string $from, string $to, bool $mode = false): array
    {
        $from = new DateTimeImmutable($from);
        $to = (new DateTimeImmutable($to))->modify('+1 day');
        $period = new DatePeriod(
            start: $from,
            interval: new DateInterval('P1D'),
            end: $to
        );

        $dates = iterator_to_array($period);
        $formattedDates = array_map(
            static fn (DateTimeImmutable $date) => $date->format(DateFormatConstant::DATE->value),
            $dates
        );

        if ($mode) {
            return array_map(
                static fn (string $date) => (new DateTimeImmutable($date))->format('D, d.m'),
                $formattedDates
            );
        }

        return $formattedDates;
    }

    public static function nextWeek(string $week): string
    {
        return (new DateTimeImmutable($week))
            ->modify('+1 week')
            ->format(DateFormatConstant::DATABASE->value);
    }

    public static function previousWeek(string $week): string
    {
        return (new DateTimeImmutable($week))
            ->modify('-1 week')
            ->format(DateFormatConstant::DATABASE->value);
    }

    public static function endWeek(string $week): string
    {
        return (new DateTimeImmutable($week))
            ->modify('+6 days')
            ->format(DateFormatConstant::DATABASE->value);
    }

    public static function changeFormatWeek(string $format, string $date): string
    {
        return (new DateTimeImmutable($date))->format($format);
    }

    public static function checkWeek(string $date): string
    {
        $dateTime = new DateTimeImmutable($date);
        $startOfWeek = $dateTime->modify('this week monday');

        return $startOfWeek->format(DateFormatConstant::DATE->value);
    }

    public static function validateDate(string $format, string $date): bool
    {
        $dateTime = DateTimeImmutable::createFromFormat($format, $date);

        return $dateTime && $dateTime->format($format) === $date;
    }

    public static function firstDayMonth(): string
    {
        return (new DateTime('first day of this month'))
            ->format(DateFormatConstant::DATABASE->value);
    }

    public static function lastDayMonth(): string
    {
        return (new DateTime('last day of this month'))
            ->format(DateFormatConstant::DATABASE->value);
    }

    public static function plusDayDate(string $date, int $quantity): string
    {
        return (new DateTimeImmutable($date))
            ->modify("+{$quantity} days")
            ->format(DateFormatConstant::DATABASE->value);
    }

    public static function minusDayDate(string $date, int $quantity): string
    {
        return (new DateTimeImmutable($date))
            ->modify("-{$quantity} days")
            ->format(DateFormatConstant::DATABASE->value);
    }
}
