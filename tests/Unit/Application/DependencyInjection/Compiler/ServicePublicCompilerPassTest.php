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

namespace App\Tests\Unit\Application\DependencyInjection\Compiler;

use App\Application\DependencyInjection\Compiler\ServicePublicCompilerPass;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServicePublicCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder;

        foreach (ServicePublicCompilerPass::SERVICES as $service) {
            $container
                ->register($service)
                ->setPublic(false);
        }

        foreach (ServicePublicCompilerPass::SERVICES as $service) {
            $definition = $container->getDefinition($service);

            $this->assertFalse($definition->isPublic());
        }

        $resolvePass = new ServicePublicCompilerPass;
        $resolvePass->process($container);

        foreach (ServicePublicCompilerPass::SERVICES as $service) {
            $definition = $container->getDefinition($service);

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
