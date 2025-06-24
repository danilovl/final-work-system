<?php declare(strict_types=1);

namespace App\Tests\Unit\Application\Provider;

use App\Application\Provider\LoggableProvider;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use PHPUnit\Framework\TestCase;

class LoggableProviderTest extends TestCase
{
    public function testIsEnableReturnsTrueWhenLoggableEnabled(): void
    {
        $parameterServiceMock = $this->createMock(ParameterServiceInterface::class);

        $parameterServiceMock->method('getBoolean')
            ->with('loggable.enable')
            ->willReturn(true);

        $loggableProvider = new LoggableProvider($parameterServiceMock);

        $this->assertTrue($loggableProvider->isEnable());
    }

    public function testIsEnableReturnsFalseWhenLoggableDisabled(): void
    {
        $parameterServiceMock = $this->createMock(ParameterServiceInterface::class);

        $parameterServiceMock->method('getBoolean')
            ->with('loggable.enable')
            ->willReturn(false);

        $loggableProvider = new LoggableProvider($parameterServiceMock);

        $this->assertFalse($loggableProvider->isEnable());
    }
}
