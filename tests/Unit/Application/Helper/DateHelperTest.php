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

namespace App\Tests\Unit\Application\Helper;

use App\Application\Constant\DateFormatConstant;
use App\Application\Helper\DateHelper;
use DateTime;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{
    public function testActualDay(): void
    {
        $actualDay = DateHelper::actualDay();
        $expectedFormat = 'Y-m-d H:i:s';

        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $actualDay);
        $this->assertTrue(DateHelper::validateDate($expectedFormat, $actualDay));
    }

    public function testActualWeekStartByDate(): void
    {
        $date = new DateTime('2023-07-15');
        $expectedWeekStart = new DateTime('2023-07-10');

        $actualWeekStart = DateHelper::actualWeekStartByDate($date);

        $this->assertEquals($expectedWeekStart, $actualWeekStart);

        $date = new DateTime('2023-07-16');
        $expectedWeekStart = new DateTime('2023-07-10');

        $actualWeekStart = DateHelper::actualWeekStartByDate($date);

        $this->assertEquals($expectedWeekStart, $actualWeekStart);
    }

    public function testActualWeekStart(): void
    {
        $actualWeekStart = DateHelper::actualWeekStart();
        $expectedFormat = 'Y-m-d H:i:s';

        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $actualWeekStart);
        $this->assertTrue(DateHelper::validateDate($expectedFormat, $actualWeekStart));
    }

    public function testActualWeekEnd(): void
    {
        $actualWeekEnd = DateHelper::actualWeekEnd();
        $expectedFormat = 'Y-m-d H:i:s';

        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $actualWeekEnd);
        $this->assertTrue(DateHelper::validateDate($expectedFormat, $actualWeekEnd));
    }

    public function testDatePeriod(): void
    {
        $from = '2023-07-01';
        $to = '2023-07-07';
        $expectedDates = ['2023-07-01', '2023-07-02', '2023-07-03', '2023-07-04', '2023-07-05', '2023-07-06', '2023-07-07'];

        $actualDates = DateHelper::datePeriod($from, $to);

        $this->assertEquals($expectedDates, $actualDates);

        $from = '2023-07-01';
        $to = '2023-07-07';
        $expectedDates = ['Sat, 01.07', 'Sun, 02.07', 'Mon, 03.07', 'Tue, 04.07', 'Wed, 05.07', 'Thu, 06.07', 'Fri, 07.07'];

        $actualDates = DateHelper::datePeriod($from, $to, true);

        $this->assertEquals($expectedDates, $actualDates);
    }

    public function testNextWeek(): void
    {
        $currentWeek = '2023-07-10';
        $expectedNextWeek = '2023-07-17 00:00:00';

        $actualNextWeek = DateHelper::nextWeek($currentWeek);

        $this->assertEquals($expectedNextWeek, $actualNextWeek);
    }

    public function testPreviousWeek(): void
    {
        $currentWeek = '2023-07-10';
        $expectedPreviousWeek = '2023-07-03 00:00:00';

        $actualPreviousWeek = DateHelper::previousWeek($currentWeek);

        $this->assertEquals($expectedPreviousWeek, $actualPreviousWeek);
    }

    public function testEndWeek(): void
    {
        $currentWeek = '2023-07-10';
        $expectedEndWeek = '2023-07-16 00:00:00';

        $actualEndWeek = DateHelper::endWeek($currentWeek);

        $this->assertEquals($expectedEndWeek, $actualEndWeek);
    }

    public function testChangeFormatWeek(): void
    {
        $format = 'd/m/Y';
        $date = '2023-07-07';
        $expectedResult = '07/07/2023';

        $changedDate = DateHelper::changeFormatWeek($format, $date);

        $this->assertEquals($expectedResult, $changedDate);
    }

    public function testCheckWeek(): void
    {
        $date = '2023-07-07';
        $expectedResult = '2023-07-03';

        $actualWeek = DateHelper::checkWeek($date);

        $this->assertEquals($expectedResult, $actualWeek);

        $date = '2023-07-24';
        $expectedResult = $date;

        $actualWeek = DateHelper::checkWeek($date);

        $this->assertEquals($expectedResult, $actualWeek);
    }

    public function testValidateDate(): void
    {
        $format = DateFormatConstant::DATE->value;
        $date = '2023-07-07';

        $isValid = DateHelper::validateDate($format, $date);

        $this->assertTrue($isValid);
    }

    public function testFirstDayMonth(): void
    {
        $expectedFormat = DateFormatConstant::DATABASE->value;

        $firstDayMonth = DateHelper::firstDayMonth();

        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $firstDayMonth);
        $this->assertTrue(DateHelper::validateDate($expectedFormat, $firstDayMonth));
    }

    public function testLastDayMonth(): void
    {
        $expectedFormat = DateFormatConstant::DATABASE->value;

        $lastDayMonth = DateHelper::lastDayMonth();

        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $lastDayMonth);
        $this->assertTrue(DateHelper::validateDate($expectedFormat, $lastDayMonth));
    }

    public function testPlusDayDate(): void
    {
        $date = '2023-07-07';
        $quantity = 5;
        $expectedResult = '2023-07-12 00:00:00';

        $plusDayDate = DateHelper::plusDayDate($date, $quantity);

        $this->assertEquals($expectedResult, $plusDayDate);
    }

    public function testMinusDayDate(): void
    {
        $date = '2023-07-07';
        $quantity = 3;
        $expectedResult = '2023-07-04 00:00:00';

        $minusDayDate = DateHelper::minusDayDate($date, $quantity);

        $this->assertEquals($expectedResult, $minusDayDate);
    }
}
