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

namespace App\Tests\Unit\Infrastructure\Config\DependencyInjection\Boot;

use App\Application\Provider\OpenTelemetryProvider;
use App\Infrastructure\Config\DependencyInjection\Boot\OpenTelemetryBoot;
use App\Infrastructure\OpenTelemetry\OpenTelemetryManager;
use App\Tests\Mock\Unit\Infrastructure\OpenTelemetry\{
    MockRegistrationA,
    MockRegistrationB
};
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class OpenTelemetryBootTest extends TestCase
{
    protected function tearDown(): void
    {
        MockRegistrationA::$called = 0;
        MockRegistrationB::$called = 0;
    }

    public function testProcessDoesNothingWhenProviderDisabled(): void
    {
        $provider = $this->createMock(OpenTelemetryProvider::class);
        $provider->expects($this->once())
            ->method('isEnable')
            ->willReturn(false);

        $container = $this->createStub(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnCallback(function (string $id) use ($provider) {
                if ($id === OpenTelemetryProvider::class) {
                    return $provider;
                }
                $this->fail(sprintf('Container should not request "%s" when provider disabled', $id));
            });

        OpenTelemetryBoot::process($container);

        $this->assertSame(0, MockRegistrationA::$called);
        $this->assertSame(0, MockRegistrationB::$called);
    }

    public function testProcessInvokesAllRegistrationsWhenEnabled(): void
    {
        $provider = $this->createMock(OpenTelemetryProvider::class);
        $provider->expects($this->once())
            ->method('isEnable')
            ->willReturn(true);

        $manager = new OpenTelemetryManager;
        $manager->addRegistration(MockRegistrationA::class);
        $manager->addRegistration(MockRegistrationB::class);

        $container = $this->createStub(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnCallback(function (string $id) use ($provider, $manager) {
                return match ($id) {
                    OpenTelemetryProvider::class => $provider,
                    OpenTelemetryManager::class => $manager,
                    MockRegistrationA::class => new MockRegistrationA,
                    MockRegistrationB::class => new MockRegistrationB,
                    default => $this->fail(sprintf('Unexpected container get for id "%s"', $id))
                };
            });

        OpenTelemetryBoot::process($container);

        $this->assertSame(1, MockRegistrationA::$called);
        $this->assertSame(1, MockRegistrationB::$called);
    }
}
