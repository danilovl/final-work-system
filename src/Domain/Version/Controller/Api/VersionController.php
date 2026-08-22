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

namespace App\Domain\Version\Controller\Api;

use App\Domain\Version\Http\Api\VersionListHandle;
use App\Domain\Work\Entity\Work;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

#[OA\Tag(name: 'Version')]
readonly class VersionController
{
    public function __construct(private VersionListHandle $versionListHandle) {}

    #[OA\Get(
        path: '/api/key/versions/works/{id}',
        description: 'Retrieves paginated list of versions (files) for the specified work.',
        summary: 'List versions by work'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Work ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 123)
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number (starts from 1)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 20)
    )]
    #[OA\Response(
        response: 200,
        description: 'Paginated versions list for the work',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'count', type: 'integer', example: 3),
                new OA\Property(property: 'totalCount', type: 'integer', example: 12),
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'result',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 101),
                            new OA\Property(property: 'originalMediaName', type: 'string', example: 'thesis_v1.pdf'),
                            new OA\Property(property: 'originalExtension', type: 'string', example: 'pdf'),
                            new OA\Property(property: 'mediaSize', type: 'integer', example: 2_048_000),
                            new OA\Property(property: 'createdAt', type: 'string', example: '2024-01-10 12:00:00')
                        ],
                        type: 'object'
                    )
                )
            ],
            type: 'object'
        )
    )]
    public function list(Request $request, Work $work): JsonResponse
    {
        return $this->versionListHandle->__invoke($request, $work);
    }
}
