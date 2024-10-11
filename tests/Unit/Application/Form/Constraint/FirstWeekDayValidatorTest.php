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
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class FirstWeekDayValidatorTest extends ConstraintValidatorTestCase
{
    private const string MESSAGE = 'It is not first day of the week';

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

    #[DataProvider('validateSuccessProvider')]
    public function testValidateSuccess(string $startDate): void
    {
        $date = new DateTime($startDate);
        $this->validator->initialize($this->context);
        $this->validator->validate($date, new FirstWeekDay);

        $this->assertNoViolation();
    }

    #[DataProvider('validateFailedProvider')]
    public function testValidateFailed(string $startDate): void
    {
        $date = new DateTime($startDate);
        $this->validator->initialize($this->context);
        $this->validator->validate($date, new FirstWeekDay);

        $this->assertEquals(1, $this->context->getViolations()->count());
    }

    public function testValueNull(): void
    {
        $this->validator->initialize($this->context);
        $this->validator->validate(null, new FirstWeekDay);

        $this->assertNoViolation();
    }

    public function testFirstWeekDay(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->initialize($this->context);
        $this->validator->validate(new DateTime, new class extends Constraint {});
    }

    public function testDateTimeType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->initialize($this->context);
        $this->validator->validate(1234, new FirstWeekDay);
    }

    public static function validateSuccessProvider(): Generator
    {
        yield ['2019-01-07'];
        yield ['2019-01-14'];
        yield ['2019-01-21'];
        yield ['2019-01-28'];
    }

    public static function validateFailedProvider(): Generator
    {
        yield ['2019-01-05'];
        yield ['2019-02-14'];
        yield ['2019-03-21'];
        yield ['2019-04-11'];
    }
}
