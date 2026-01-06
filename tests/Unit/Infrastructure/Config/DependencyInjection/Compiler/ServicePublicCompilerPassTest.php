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

use App\Infrastructure\Config\DependencyInjection\Compiler\ServicePublicCompilerPass;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServicePublicCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $services = ServicePublicCompilerPass::SERVICES;
        $servicesDefinitions = array_slice($services, 1);
        $servicesAliases = array_slice($services, 0, 1);

        $container = new ContainerBuilder;

        foreach ($servicesAliases as $service) {
            $container
                ->setAlias($service, 'service')
                ->setPublic(false);
        }

        foreach ($servicesDefinitions as $service) {
            $container
                ->register($service)
                ->setPublic(false);
        }

        foreach ($servicesDefinitions as $service) {
            $definition = $container->getDefinition($service);

            $this->assertFalse($definition->isPublic());
        }

        foreach ($servicesAliases as $service) {
            $definition = $container->getAlias($service);

            $this->assertFalse($definition->isPublic());
        }

        $resolvePass = new ServicePublicCompilerPass;
        $resolvePass->process($container);

        foreach ($servicesDefinitions as $service) {
            $definition = $container->getDefinition($service);

            $this->assertTrue($definition->isPublic());
        }

        foreach ($servicesAliases as $service) {
            $definition = $container->getAlias($service);

            $this->assertTrue($definition->isPublic());
        }
    }

    public function testEasyAdminFix(): void
    {
        $container = new ContainerBuilder;
        $container
            ->register('EasyAdmin', EasyAdminBundle::class)
            ->setPublic(false);

        $compilerPass = new ServicePublicCompilerPass;
        $compilerPass->easyAdminFix($container);

        $definition = $container->getDefinition('EasyAdmin');

        $this->assertTrue($definition->isPublic());
    }
}
