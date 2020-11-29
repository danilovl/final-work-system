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

use App\Constant\DateFormatConstant;
use DatePeriod;
use DateTime;
use DateInterval;

class DateHelper
{
    /**
     * @return false|string
     */
    public static function actualDay()
    {
        return date(DateFormatConstant::DATABASE);
    }

    public static function actualWeekStartByDate(DateTime $date): DateTime
    {
        return $date->sub(new DateInterval('P' . ($date->format('w') - 1) . 'D'));
    }

    /**
     * @return false|string
     */
    public static function actualWeekStart()
    {
        $cur_time = time();
        $week_start = date('w') === 1 ? strtotime('0 hours 0 seconds') : strtotime('last Monday', mktime(0, 0, 0, date('n', $cur_time), date('j', $cur_time), date('Y', $cur_time)));

        return date(DateFormatConstant::DATABASE, $week_start);
    }

    /**
     * @return false|string
     */
    public static function actualWeekEnd()
    {
        $cur_time = time();
        $week_start = date('w') === 1 ? strtotime('0 hours 0 seconds') : strtotime('last Monday', mktime(0, 0, 0, date('n', $cur_time), date('j', $cur_time), date('Y', $cur_time)));
        $week_end = $week_start + 6 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE, $week_end);
    }

    public static function datePeriod($from, $to, $mode = false): array
    {
        $from = new DateTime($from);
        $to = new DateTime($to);
        $to = $to->modify('+1 day');

        $period = new DatePeriod($from, new DateInterval('P1D'), $to);

        $arrayOfDates = array_map(static fn($item) => $item->format(DateFormatConstant::DATE), iterator_to_array($period));

        if ($mode === true) {
            $arrayNameDay = [];
            foreach ($arrayOfDates as $date) {
                $arrayNameDay[] = strftime('%a, %d.%m', strtotime($date));
            }
            return $arrayNameDay;
        }

        return $arrayOfDates;
    }

    /**
     * @param $week
     * @return false|string
     */
    public static function nextWeek($week)
    {
        $nextWeek = strtotime($week);
        $nextWeek += 7 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE, $nextWeek);
    }

    /**
     * @param $week
     * @return false|string
     */
    public static function previousWeek($week)
    {
        $previousWeek = strtotime($week);
        $previousWeek -= 7 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE, $previousWeek);
    }

    /**
     * @param $week
     * @return false|string
     */
    public static function endWeek($week)
    {
        $endWeek = strtotime($week);
        $endWeek += 6 * 24 * 60 * 60;

        return date(DateFormatConstant::DATABASE, $endWeek);
    }

    /**
     * @param $format
     * @param $date
     * @return false|string
     */
    public static function changeFormatWeek($format, $date)
    {
        $changeDate = strtotime($date);

        return date($format, $changeDate);
    }

    /**
     * @param $date
     * @return false|string
     */
    public static function checkWeek($date)
    {
        $current_time = strtotime($date);
        $actualWeek = date('d.m.Y', $current_time - (date('N', $current_time) - 1) * 86400);
        if ($actualWeek === $date) {
            return $date;
        }

        return $actualWeek;
    }

    public static function validateDate($format, $date): bool
    {
        return date($format, strtotime($date)) === $date;
    }

    /**
     * @return false|string
     */
    public static function firstDayMonth()
    {
        return date(DateFormatConstant::DATABASE, strtotime('first day of this month'));
    }

    /**
     * @return false|string
     */
    public static function lastDayMonth()
    {
        return date(DateFormatConstant::DATABASE, strtotime('last day of this month'));
    }

    /**
     * @param $date
     * @param $quantity
     * @return false|string
     */
    public static function plusDayDate($date, $quantity)
    {
        return date(DateFormatConstant::DATABASE, strtotime($date . ' + ' . $quantity . ' days'));
    }

    /**
     * @param $date
     * @param $quantity
     * @return false|string
     */
    public static function minusDayDate($date, $quantity)
    {
        return date(DateFormatConstant::DATABASE, strtotime($date . ' - ' . $quantity . ' days'));
    }
}
 