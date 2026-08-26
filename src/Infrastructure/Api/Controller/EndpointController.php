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

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Routing\RouterInterface;

#[OA\Tag(name: 'Endpoint')]
readonly class EndpointController
{
    private const string ROUTE_PREFIX_KEY = 'api_key';

    public function __construct(private RouterInterface $router) {}

    #[OA\Get(
        path: '/api/key/endpoint/list',
        description: 'Returns a map of API key routes with their absolute URLs.',
        summary: 'Endpoint list'
    )]
    #[OA\Response(
        response: 200,
        description: 'A JSON object where keys are route names and values are absolute URLs',
        content: new OA\JsonContent(
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'string')
        )
    )]
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
