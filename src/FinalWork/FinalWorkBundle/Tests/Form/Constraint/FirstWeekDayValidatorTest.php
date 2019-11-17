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

namespace FinalWork\FinalWorkBundle\Tests\Form\Constraint;

use DateTime;
use Exception;
use FinalWork\FinalWorkBundle\Form\Constraint\{
    FirstWeekDay,
    FirstWeekDayValidator
};
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class FirstWeekDayValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var string
     */
    private const MESSAGE = 'It is not first day of the week';

    /**
     * @return FirstWeekDayValidator
     */
    protected function createValidator(): FirstWeekDayValidator
    {
        return new FirstWeekDayValidator($this->getTransMock());
    }

    /**
     * @return MockObject|TranslatorInterface
     */
    private function getTransMock(): MockObject
    {
        $mockObject = $this->createMock(TranslatorInterface::class);
        $mockObject->expects($this->any())
            ->method('trans')
            ->willReturn(self::MESSAGE);

        return $mockObject;
    }

    /**
     * @dataProvider firstProvider
     * @param string $startDate
     * @throws Exception
     */
    public function testIsValid(string $startDate): void
    {
        $date = new DateTime($startDate);
        $this->validator->initialize($this->context);
        $this->validator->validate($date, new FirstWeekDay);

        $this->assertNoViolation();
    }

    /**
     * @return Generator
     */
    public function firstProvider(): Generator
    {
        yield ['2019-01-07'];
        yield ['2019-01-14'];
        yield ['2019-01-21'];
        yield ['2019-01-28'];
    }
}