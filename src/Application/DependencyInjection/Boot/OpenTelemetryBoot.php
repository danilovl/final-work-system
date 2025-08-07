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

namespace App\Application\DependencyInjection\Boot;

use App\Application\OpenTelemetry\{
    OpenTelemetryManager,
    OpenTelemetryRegistrationInterface
};
use Psr\Container\ContainerInterface;

class OpenTelemetryBoot
{
    public static function process(ContainerInterface $container): void
    {
        /** @var OpenTelemetryManager $manager */
        $manager = $container->get(OpenTelemetryManager::class);

        foreach ($manager->getOpenTelemetryRegistrations() as $registrationClass) {
            /** @var OpenTelemetryRegistrationInterface $registration */
            $registration = $container->get($registrationClass);
            $registration->registration();
        }
    }
}
