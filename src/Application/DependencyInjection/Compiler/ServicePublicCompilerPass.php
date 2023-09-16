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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Messenger\MessageBusInterface;

class ServicePublicCompilerPass implements CompilerPassInterface
{
    public const SERVICES = [
        'twig',
        'form.factory',
        'security.authorization_checker',
        'fos_elastica.finder.work',
        MessageBusInterface::class
    ];

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

        $this->easyAdminFix($container);
    }

    public function easyAdminFix(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            if ($definition->getClass() === null) {
                continue;
            }

            if (str_contains($definition->getClass(), 'EasyCorp')) {
                $definition->setPublic(true);
            }
        }
    }
}
