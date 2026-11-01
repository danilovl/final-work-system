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

namespace App\Tests\Unit\Infrastructure\Config\DependencyInjection\Compiler;

use App\Infrastructure\Config\DependencyInjection\Compiler\OpenTelemetryCompilerPass;
use App\Infrastructure\OpenTelemetry\OpenTelemetryManager;
use App\Infrastructure\OpenTelemetry\Async\AsyncRegistration;
use App\Infrastructure\OpenTelemetry\Command\CommandRegistration;
use App\Infrastructure\OpenTelemetry\HttpClient\HttpClientRegistration;
use App\Tests\Mock\Unit\Infrastructure\Config\DependencyInjection\Compiler\{
    DummyNonImplementing,
    MockOpenTelemetryExtensionProvider
};
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Definition
};

class OpenTelemetryCompilerPassTest extends TestCase
{
    private MockOpenTelemetryExtensionProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new MockOpenTelemetryExtensionProvider(false);
    }

    public function testSkipsExtensionNotLoaded(): void
    {
        $container = new ContainerBuilder;
        $container->setDefinition(OpenTelemetryManager::class, new Definition(OpenTelemetryManager::class));

        $definition = new Definition(AsyncRegistration::class);
        $definition->addTag('app.open_telemetry.registration', ['priority' => 10]);
        $container->setDefinition(AsyncRegistration::class, $definition);

        $this->provider->setLoaded(false);
        $provider = $this->provider;

        $compilerPass = new OpenTelemetryCompilerPass($provider);
        $compilerPass->process($container);

        /** @var OpenTelemetryManager $manager */
        $manager = $container->get(OpenTelemetryManager::class);
        $this->assertSame([], $manager->getOpenTelemetryRegistrations());
    }

    public function testPriorityOrder(): void
    {
        $container = new ContainerBuilder;
        $container->setDefinition(OpenTelemetryManager::class, new Definition(OpenTelemetryManager::class));

        $definitionLow = new Definition(CommandRegistration::class);
        $definitionLow->addTag('app.open_telemetry.registration', ['priority' => 5]);
        $container->setDefinition(CommandRegistration::class, $definitionLow);

        $definitionMid = new Definition(AsyncRegistration::class);
        $definitionMid->addTag('app.open_telemetry.registration', ['priority' => 10]);
        $container->setDefinition(AsyncRegistration::class, $definitionMid);

        $definitionHigh = new Definition(HttpClientRegistration::class);
        $definitionHigh->addTag('app.open_telemetry.registration', ['priority' => 20]);
        $container->setDefinition(HttpClientRegistration::class, $definitionHigh);

        $this->provider->setLoaded(true);
        $provider = $this->provider;

        $pass = new OpenTelemetryCompilerPass($provider);
        $pass->process($container);

        /** @var OpenTelemetryManager $manager */
        $manager = $container->get(OpenTelemetryManager::class);

        $expected = [
            HttpClientRegistration::class,
            AsyncRegistration::class,
            CommandRegistration::class,
        ];

        $this->assertSame($expected, $manager->getOpenTelemetryRegistrations());
    }

    public function testMissingPriority(): void
    {
        $container = new ContainerBuilder;
        $container->setDefinition(OpenTelemetryManager::class, new Definition(OpenTelemetryManager::class));

        $defNon = new Definition(DummyNonImplementing::class);
        $defNon->addTag('app.open_telemetry.registration', ['priority' => 50]);
        $container->setDefinition(DummyNonImplementing::class, $defNon);

        $this->provider->setLoaded(true);
        $provider = $this->provider;

        $pass = new OpenTelemetryCompilerPass($provider);
        $pass->process($container);

        /** @var OpenTelemetryManager $manager */
        $manager = $container->get(OpenTelemetryManager::class);
        $this->assertSame([], $manager->getOpenTelemetryRegistrations());
    }
}
