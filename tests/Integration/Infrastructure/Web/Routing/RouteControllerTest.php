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

namespace App\Tests\Integration\Infrastructure\Web\Routing;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * Every route must point to an existing controller class and method.
 * A wrong namespace in YAML routing is not caught by lint:container or by cache warmup.
 */
class RouteControllerTest extends KernelTestCase
{
    public function testEveryRouteControllerExists(): void
    {
        $container = self::bootKernel()->getContainer();
        /** @var RouterInterface $router */
        $router = $container->get('router');

        $broken = [];
        $checked = 0;

        foreach ($router->getRouteCollection() as $name => $route) {
            $controller = $route->getDefault('_controller');
            if (!is_string($controller)) {
                continue;
            }

            $checked++;
            [$class, $method] = array_pad(explode('::', $controller, 2), 2, '__invoke');

            if (!class_exists($class)) {
                if (!$container->has($class)) {
                    $broken[] = sprintf('%s: class %s does not exist', $name, $class);
                }

                continue;
            }

            if (!method_exists($class, $method)) {
                $broken[] = sprintf('%s: method %s::%s does not exist', $name, $class, $method);
            }
        }

        $this->assertGreaterThan(0, $checked);
        $this->assertSame([], $broken);
    }
}
