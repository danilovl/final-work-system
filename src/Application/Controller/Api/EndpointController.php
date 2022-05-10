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

namespace App\Application\Controller\Api;

use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class EndpointController extends AbstractController
{
    private const ROUTE_PREFIX_KEY = 'api_key';

    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function list(Request $request): JsonResponse
    {
        /** @var Route[] $routes */
        $routes = $this->router->getRouteCollection();

        $result = [];
        foreach ($routes as $routeKey => $route) {
            if (!str_contains($routeKey, self::ROUTE_PREFIX_KEY)) {
                continue;
            }

            $url = $request->getSchemeAndHttpHost() . $route->getPath();
            $url = rtrim($url, '/');

            $result[$routeKey] = $url;
        }

        return new JsonResponse($result);
    }
}
