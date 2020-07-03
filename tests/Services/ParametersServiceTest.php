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

namespace App\Tests\Services;

use Danilovl\ParameterBundle\Services\ParameterService;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParametersServiceTest extends KernelTestCase
{
    /**
     * @var ParameterService
     */
    private ParameterService $parametersService;

    public function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->parametersService = new ParameterService($kernel->getContainer());
    }

    /**
     * @dataProvider keyIntegerProvider
     */
    public function testKeyInteger(string $key, int $result): void
    {
        $param = $this->parametersService->get($key);
        $this->assertEquals($param, $result);
    }

    /**
     * @dataProvider keyStringProvider
     */
    public function testKeyString(string $key, string $result): void
    {
        $param = $this->parametersService->get($key);
        $this->assertEquals($param, $result);
    }

    /**
     * @dataProvider keyArrayProvider
     */
    public function testKeyArray(string $key, array $result): void
    {
        $param = $this->parametersService->get($key);
        $this->assertEquals($param, $result);
    }

    public function keyIntegerProvider(): Generator
    {
        yield ['pagination.default.page', 1];
        yield ['pagination.default.limit', 25];
        yield ['pagination.home.limit', 100];
    }

    public function keyStringProvider(): Generator
    {
        yield ['template.ajax', 'ajax/'];
        yield ['locale', 'cs'];
        yield ['locales', 'en|cs|ru'];
    }

    public function keyArrayProvider(): Generator
    {
        yield ['pagination.default', ['page' => 1, 'limit' => 25]];
    }
}
