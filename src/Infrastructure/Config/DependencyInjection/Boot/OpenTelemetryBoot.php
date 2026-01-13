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

namespace App\Infrastructure\Config\DependencyInjection\Boot;

use App\Application\Provider\OpenTelemetryProvider;
use App\Infrastructure\OpenTelemetry\{
    OpenTelemetryManager};
use App\Infrastructure\OpenTelemetry\OpenTelemetryRegistrationInterface;
use Psr\Container\ContainerInterface;

class OpenTelemetryBoot
{
    public static function process(ContainerInterface $container): void
    {
        /** @var OpenTelemetryProvider $openTelemetryProvider */
        $openTelemetryProvider = $container->get(OpenTelemetryProvider::class);
        if (!$openTelemetryProvider->isEnable()) {
            return;
        }

        /** @var OpenTelemetryManager $manager */
        $manager = $container->get(OpenTelemetryManager::class);

        foreach ($manager->getOpenTelemetryRegistrations() as $registrationClass) {
            /** @var OpenTelemetryRegistrationInterface $registration */
            $registration = $container->get($registrationClass);
            $registration->registration();
        }
    }
}
