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

use App\Infrastructure\Config\DependencyInjection\Boot\OpenTelemetryBoot;
use App\Infrastructure\Config\DependencyInjection\Compiler\{
    WidgetCompilerPass,
    OpenTelemetryCompilerPass,
    ServicePublicCompilerPass
};
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new OpenTelemetryCompilerPass);
        $container->addCompilerPass(new ServicePublicCompilerPass);
        $container->addCompilerPass(new WidgetCompilerPass);
    }

    public function boot(): void
    {
        /** @var ContainerInterface $container */
        $container = $this->container;

        OpenTelemetryBoot::process($container);
    }
}
