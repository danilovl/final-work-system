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

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServicePublicCompilerPass implements CompilerPassInterface
{
    private const SERVICES = [
        'fos_elastica.finder.work'
    ];

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        foreach (self::SERVICES as $service) {
            if ($container->hasDefinition($service)) {
                $container->getDefinition($service)->setPublic(true);
            }

            if ($container->hasAlias($service)) {
                $container->getAlias($service)->setPublic(true);
            }
        }
    }
}
