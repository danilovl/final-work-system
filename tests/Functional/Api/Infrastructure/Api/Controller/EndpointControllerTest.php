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

namespace App\Tests\Functional\Api\Infrastructure\Api\Controller;

use App\Tests\Functional\Api\BaseApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class EndpointControllerTest extends BaseApiTestCase
{
    public function testListContainsAllApiKeyRoutes(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/endpoint/list', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        /** @var RouterInterface $router */
        $router = static::getContainer()->get(RouterInterface::class);
        $routeCollection = $router->getRouteCollection();

        $expectedRouteNames = [];
        foreach ($routeCollection->all() as $routeName => $route) {
            $path = $route->getPath();
            if (str_starts_with($path, '/api/key/')) {
                $expectedRouteNames[] = $routeName;
            }
        }

        $routeNames = array_keys($data);
        sort($expectedRouteNames);
        sort($routeNames);

        $this->assertNotEmpty($routeNames);
        $this->assertSame($expectedRouteNames, $routeNames);

        $taskDetailUrl = $this->getString($data, 'api_key_get_task_detail');
        $this->assertStringEndsWith('/api/key/tasks/{id_task}/works/{id_work}', $taskDetailUrl);
    }
}
