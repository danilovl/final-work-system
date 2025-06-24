<?php declare(strict_types=1);

namespace App\Tests\Unit\Application\Provider;

use App\Application\Provider\ElasticApmProvider;
use App\Application\Service\IniService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use PHPUnit\Framework\TestCase;

class ElasticApmProviderTest extends TestCase
{
    public function testIsEnable(): void
    {
        $parameterServiceMock = $this->createMock(ParameterServiceInterface::class);
        $parameterServiceMock->method('getBoolean')
            ->with('apm.enable')
            ->willReturn(true);

        $iniServiceMock = $this->createMock(IniService::class);
        $iniServiceMock->method('get')
            ->with('elastic_apm.enabled')
            ->willReturn('1');

        $elasticApmProvider = new ElasticApmProvider($parameterServiceMock, $iniServiceMock);

        $this->assertTrue($elasticApmProvider->isEnable());
    }

    public function testIsNotEnable(): void
    {
        $iniServiceMock = $this->createMock(IniService::class);

        $parameterServiceMock = $this->createMock(ParameterServiceInterface::class);
        $parameterServiceMock->method('getBoolean')
            ->with('apm.enable')
            ->willReturn(false);

        $elasticApmProvider = new ElasticApmProvider($parameterServiceMock, $iniServiceMock);

        $this->assertFalse($elasticApmProvider->isEnable());
    }
}
