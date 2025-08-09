<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Tests\Unit\Application\Provider;

use App\Application\Provider\OpenTelemetryProvider;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use PHPUnit\Framework\TestCase;

class OpenTelemetryProviderTest extends TestCase
{
    private ParameterServiceInterface $parameterService;

    private OpenTelemetryProvider $openTelemetryProvider;

    protected function setUp(): void
    {
        $this->parameterService = $this->createMock(ParameterServiceInterface::class);
        $this->openTelemetryProvider = new OpenTelemetryProvider($this->parameterService);
    }

    public function testIsEnableTrue(): void
    {
        $this->parameterService
            ->expects($this->once())
            ->method('getBoolean')
            ->with('open_telemetry.enable')
            ->willReturn(true);

        $this->assertTrue($this->openTelemetryProvider->isEnable());
    }

    public function testIsEnableFalse(): void
    {
        $this->parameterService
            ->expects($this->once())
            ->method('getBoolean')
            ->with('open_telemetry.enable')
            ->willReturn(false);

        $this->assertFalse($this->openTelemetryProvider->isEnable());
    }
}
