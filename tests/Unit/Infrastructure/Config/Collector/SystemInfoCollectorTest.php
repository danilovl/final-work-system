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

namespace App\Tests\Unit\Infrastructure\Config\Collector;

use App\Infrastructure\Config\Collector\SystemInfoCollector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class SystemInfoCollectorTest extends TestCase
{
    private SystemInfoCollector $collector;

    protected function setUp(): void
    {
        $systemInfo = [
            'name' => 'Test System',
            'version' => '1.0.0'
        ];

        $request = $this->createStub(Request::class);
        $response = $this->createStub(Response::class);

        $this->collector = new SystemInfoCollector($systemInfo);
        $this->collector->collect($request, $response);
    }

    public function testCollect(): void
    {
        $this->assertEquals(SystemInfoCollector::NAME_COLLECTOR, $this->collector->getName());
        $this->assertEquals('Test System', $this->collector->getSystemName());
        $this->assertEquals('1.0.0', $this->collector->getVersion());
    }

    public function testReset(): void
    {
        $this->expectNotToPerformAssertions();

        $this->collector->reset();
    }
}
