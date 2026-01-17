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

namespace App\Tests\Kernel\Infrastructure\Service;

use App\Infrastructure\Service\{
    DateService,
    TranslatorService
};
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DateServiceTest extends KernelTestCase
{
    private TranslatorService $translator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->translator = $kernel->getContainer()->get(TranslatorService::class);
    }

    #[DataProvider('weekDaysProvider')]
    public function testGetWeekDaysArray(string $locale, array $arrayWeek): void
    {
        $dateService = new DateService($this->translator);

        $this->assertEquals($dateService->getWeekDaysArray($locale), $arrayWeek);
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
