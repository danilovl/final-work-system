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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServicePublicCompilerPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder;
        $container
            ->register('twig')
            ->setPublic(false);

        $twigDefinition = $container->getDefinition('twig');

        $this->assertFalse($twigDefinition->isPublic());

        $resolvePass = new ServicePublicCompilerPass;
        $resolvePass->process($container);

        $this->assertTrue($twigDefinition->isPublic());
    }
}
