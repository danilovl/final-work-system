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

namespace FinalWork\FinalWorkBundle\Tests;

use Generator;
use FinalWork\FinalWorkBundle\Services\ParametersService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParametersServiceTest extends KernelTestCase
{
    /**
     * @var ParametersService
     */
    private $parametersService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->parametersService = new ParametersService($kernel->getContainer());
    }

    /**
     * @dataProvider keyIntegerProvider
     * @param string $key
     * @param int $result
     */
    public function testKeyInteger(string $key, int $result): void
    {
        $param = $this->parametersService->getParam($key);
        $this->assertEquals($param, $result);
    }

    /**
     * @dataProvider keyStringProvider
     * @param string $key
     * @param string $result
     */
    public function testKeyString(string $key, string $result): void
    {
        $param = $this->parametersService->getParam($key);
        $this->assertEquals($param, $result);
    }

    /**
     * @dataProvider keyArrayProvider
     * @param string $key
     * @param array $result
     */
    public function testKeyArray(string $key, array $result): void
    {
        $param = $this->parametersService->getParam($key);
        $this->assertEquals($param, $result);
    }

    /**
     * @return Generator
     */
    public function keyIntegerProvider(): Generator
    {
        yield ['pagination.default.page', 1];
        yield ['pagination.default.limit', 25];
        yield ['pagination.home.limit', 100];
    }

    /**
     * @return Generator
     */
    public function keyStringProvider(): Generator
    {
        yield ['template.ajax', 'ajax/'];
        yield ['locale', 'cs'];
        yield ['app_locales', 'en|cs|ru'];
    }

    /**
     * @return Generator
     */
    public function keyArrayProvider(): Generator
    {
        yield ['pagination.default', ['page' => 1, 'limit' => 25]];
    }
}
