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

namespace App\Domain\Work\Controller\Api;

use App\Domain\Work\DTO\Api\WorkDTO;
use App\Domain\Work\Entity\Work;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use App\Domain\Work\Http\Api\{
    WorkListHandle,
    WorkDetailHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

#[OA\Tag(name: 'Work')]
readonly class WorkController
{
    public function __construct(
        private WorkListHandle $workListHandle,
        private WorkDetailHandle $workDetailHandle
    ) {}

    #[OA\Get(
        path: '/api/key/works/{type}',
        description: 'Retrieves paginated list of works for the current user filtered by relation type.',
        summary: 'List works'
    )]
    #[OA\Parameter(
        name: 'type',
        description: 'Relation type to filter works',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', enum: ['author', 'opponent', 'consultant', 'supervisor'])
    )]
    #[OA\Parameter(
        name: 'search',
        description: 'Search term to filter works by title',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
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
        description: 'Paginated works list',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'count', type: 'integer', example: 10),
                new OA\Property(property: 'totalCount', type: 'integer', example: 42),
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'result',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: WorkDTO::class))
                )
            ],
            type: 'object'
        )
    )]
    public function list(
        Request $request,
        string $type,
        #[MapQueryParameter] ?string $search = null
    ): JsonResponse {
        return $this->workListHandle->__invoke($request, $type, $search);
    }

    public function detail(Work $work): JsonResponse
    {
        return $this->workDetailHandle->__invoke($work);
    }
}
