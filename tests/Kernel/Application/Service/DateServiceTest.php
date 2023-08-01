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

namespace App\Tests\Kernel\Application\Service;

use App\Application\Service\DateService;
use App\Application\Service\TranslatorService;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DateServiceTest extends KernelTestCase
{
    private TranslatorService $translator;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->translator = $kernel->getContainer()->get(TranslatorService::class);
    }

    /**
     * @dataProvider weekDaysProvider
     */
    public function testGetWeekDaysArray(string $locale, array $arrayWeek): void
    {
        $this->translator->setLocale($locale);
        $dateService = new DateService($this->translator);

        $this->assertEquals($dateService->getWeekDaysArray(), $arrayWeek);
    }

    public static function weekDaysProvider(): Generator
    {
        yield ['cs', [
            0 => 'Po',
            1 => 'Út',
            2 => 'Stř',
            3 => 'Čt',
            4 => 'Pá',
            5 => 'So',
            6 => 'Ne'
        ]];
        yield ['en', [
            0 => 'Mo',
            1 => 'Tu',
            2 => 'We',
            3 => 'Th',
            4 => 'Fr',
            5 => 'Sa',
            6 => 'Su'
        ]];
        yield ['ru', [
            0 => 'Пн',
            1 => 'Вт',
            2 => 'Ср',
            3 => 'Чт',
            4 => 'Пт',
            5 => 'Сб',
            6 => 'Вс'
        ]];
    }
}
