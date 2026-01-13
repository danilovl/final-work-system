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

namespace App\Infrastructure\Config\DependencyInjection\Compiler;

use App\Infrastructure\OpenTelemetry\{
    OpenTelemetryManager};
use App\Infrastructure\OpenTelemetry\OpenTelemetryRegistrationInterface;
use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpenTelemetryCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        if (!extension_loaded('opentelemetry')) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds('app.open_telemetry.registration');
        $servicesWithPriority = [];

        foreach ($taggedServices as $serviceId => $tags) {
            /** @var string[] $classes */
            $classes = class_implements($serviceId);
            if (!in_array(OpenTelemetryRegistrationInterface::class, $classes)) {
                continue;
            }

            foreach ($tags as $attributes) {
                if (isset($attributes['priority'])) {
                    $priority = (int) $attributes['priority'];
                    $servicesWithPriority[] = [
                        'serviceId' => $serviceId,
                        'priority' => $priority
                    ];
                }
            }
        }

        usort($servicesWithPriority, static function (array $a, array $b): int {
            return $b['priority'] <=> $a['priority'];
        });

        $manager = $container->getDefinition(OpenTelemetryManager::class);
        foreach ($servicesWithPriority as $service) {
            $manager->addMethodCall('addRegistration', [$service['serviceId']]);
        }
    }
}
