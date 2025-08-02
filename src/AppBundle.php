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

namespace App;

use App\Application\OpenTelemetry\Elastica\ElasticaRegistration;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use App\Application\DependencyInjection\Compiler\{
    WidgetCompilerPass,
    OpenTelemetryCompilerPass,
    ServicePublicCompilerPass
};
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ServicePublicCompilerPass);
        $container->addCompilerPass(new WidgetCompilerPass);
        $container->addCompilerPass(new OpenTelemetryCompilerPass, PassConfig::TYPE_OPTIMIZE);

        $this->openTelemetryRegistration();
    }

    // Some initialization problem in OpenTelemetryCompilerPass by PassConfig::TYPE_OPTIMIZE
    private function openTelemetryRegistration(): void
    {
        if (!extension_loaded('opentelemetry')) {
            return;
        }

        ElasticaRegistration::registration();
    }
}
