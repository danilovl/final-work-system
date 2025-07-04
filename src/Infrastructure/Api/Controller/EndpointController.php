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

namespace App\Infrastructure\Api\Controller;

use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Routing\RouterInterface;

readonly class EndpointController
{
    private const string ROUTE_PREFIX_KEY = 'api_key';

    public function __construct(private RouterInterface $router) {}

    public function list(Request $request): JsonResponse
    {
        $routes = $this->router->getRouteCollection();

        $result = [];
        foreach ($routes as $routeKey => $route) {
            if (!str_contains($routeKey, self::ROUTE_PREFIX_KEY)) {
                continue;
            }

            $url = $request->getSchemeAndHttpHost() . $route->getPath();
            $url = mb_rtrim($url, '/');

            $result[$routeKey] = $url;
        }

        return new JsonResponse($result);
    }
}
