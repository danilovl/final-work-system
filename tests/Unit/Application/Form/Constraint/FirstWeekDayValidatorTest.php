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

namespace App\Tests\Unit\Application\Form\Constraint;

use App\Application\Form\Constraint\{
    FirstWeekDay,
    FirstWeekDayValidator
};
use App\Application\Service\TranslatorService;
use DateTime;
use Generator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class FirstWeekDayValidatorTest extends ConstraintValidatorTestCase
{
    private const MESSAGE = 'It is not first day of the week';

    protected function createValidator(): FirstWeekDayValidator
    {
        return new FirstWeekDayValidator($this->getTransMock());
    }

    private function getTransMock(): TranslatorService
    {
        $mockObject = $this->createMock(TranslatorService::class);
        $mockObject->expects($this->any())
            ->method('trans')
            ->willReturn(self::MESSAGE);

        return $mockObject;
    }

    /**
     * @dataProvider firstProvider
     */
    public function testIsValid(string $startDate): void
    {
        $date = new DateTime($startDate);
        $this->validator->initialize($this->context);
        $this->validator->validate($date, new FirstWeekDay);

        $this->assertNoViolation();
    }

    public function firstProvider(): Generator
    {
        yield ['2019-01-07'];
        yield ['2019-01-14'];
        yield ['2019-01-21'];
        yield ['2019-01-28'];
    }
}
