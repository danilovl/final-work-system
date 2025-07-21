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

namespace App\Application\DependencyInjection\Compiler;

use App\Application\OpenTelemetry\OpenTelemetryManager;
use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpenTelemetryCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $manager = $container->getDefinition(OpenTelemetryManager::class);
        $taggedServices = $container->findTaggedServiceIds('app.open_telemetry.registration');
        $taggedServices = array_keys($taggedServices);

        foreach ($taggedServices as $serviceId) {
            $manager->addMethodCall('addRegistration', [$serviceId]);
        }
    }
}
