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

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Work\DTO\Api\WorkDTO;
use App\Domain\Work\DTO\Api\Input\{
    WorkInput,
    WorkSearchInput
};
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Service\AuthorizationCheckerService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\{
    MapQueryString,
    MapRequestPayload
};
use App\Domain\Work\Http\Api\{
    WorkListHandle,
    WorkDetailHandle,
    WorkCreateHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

#[OA\Tag(name: 'Work')]
readonly class WorkController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private WorkListHandle $workListHandle,
        private WorkDetailHandle $workDetailHandle,
        private WorkCreateHandle $workCreateHandle
    ) {}

    #[OA\Get(
        path: '/api/key/works/{type}',
        description: 'Retrieves paginated list of works for the current user filtered by relation type and optional search criteria (matching WorkSearchForm: title, shortcut, status, type, author, supervisor, opponent, consultant, deadline).',
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
        name: 'title',
        description: 'Filter works by title',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'shortcut',
        description: 'Filter works by shortcut',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'Filter works by status ID(s)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'))
    )]
    #[OA\Parameter(
        name: 'type',
        description: 'Filter works by work type ID(s)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'))
    )]
    #[OA\Parameter(
        name: 'author',
        description: 'Filter works by author user ID(s)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'))
    )]
    #[OA\Parameter(
        name: 'supervisor',
        description: 'Filter works by supervisor user ID(s)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'))
    )]
    #[OA\Parameter(
        name: 'opponent',
        description: 'Filter works by opponent user ID(s)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'))
    )]
    #[OA\Parameter(
        name: 'consultant',
        description: 'Filter works by consultant user ID(s)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'))
    )]
    #[OA\Parameter(
        name: 'deadline',
        description: 'Filter works by deadline date string(s) (YYYY-MM-DD)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', format: 'date'))
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
        #[MapQueryString] ?WorkSearchInput $workSearchInput = null
    ): JsonResponse {
        return $this->workListHandle->__invoke(
            request: $request,
            type: $type,
            workSearchInput: $workSearchInput
        );
    }

    #[OA\Get(
        path: '/api/key/works/{id}/detail',
        description: 'Retrieves detailed information about a work item.',
        summary: 'Work detail'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Work ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 123)
    )]
    #[OA\Response(
        response: 200,
        description: 'Work detail',
        content: new OA\JsonContent(ref: new Model(type: WorkDTO::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'Work not found'
    )]
    public function detail(Work $work): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $work);

        return $this->workDetailHandle->__invoke($work);
    }

    #[OA\Post(
        path: '/api/key/works/create',
        description: 'Creates a new work.',
        summary: 'Create work'
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: WorkInput::class))
    )]
    #[OA\Response(
        response: 201,
        description: 'Work created',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'result', ref: new Model(type: WorkDTO::class))
            ],
            type: 'object'
        )
    )]
    public function create(#[MapRequestPayload] WorkInput $workInput): JsonResponse
    {
        return $this->workCreateHandle->__invoke($workInput);
    }
}
