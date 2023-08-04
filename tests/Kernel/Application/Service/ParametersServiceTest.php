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

use Danilovl\ParameterBundle\Service\ParameterService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParametersServiceTest extends KernelTestCase
{
    private ParameterService $parametersService;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->parametersService = $kernel->getContainer()->get(ParameterService::class);
    }

    #[DataProvider('keyIntegerProvider')]
    public function testKeyInteger(string $key, int $result): void
    {
        $param = $this->parametersService->get($key);
        $this->assertEquals($param, $result);
    }

    #[DataProvider('keyStringProvider')]
    public function testKeyString(string $key, string $result): void
    {
        $param = $this->parametersService->get($key);
        $this->assertEquals($param, $result);
    }

    #[DataProvider('keyArrayProvider')]
    public function testKeyArray(string $key, array $result): void
    {
        $param = $this->parametersService->get($key);
        $this->assertEquals($param, $result);
    }

    public static function keyIntegerProvider(): Generator
    {
        yield ['pagination.default.page', 1];
        yield ['pagination.default.limit', 25];
        yield ['pagination.home.limit', 100];
    }

    public static function keyStringProvider(): Generator
    {
        yield ['template.ajax', 'ajax/'];
        yield ['locale', 'en'];
        yield ['locales', 'en|cs|ru'];
    }

    public static function keyArrayProvider(): Generator
    {
        yield ['pagination.default', ['page' => 1, 'limit' => 25]];
    }
}
