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

namespace App\Domain\SystemEvent\Controller\Api;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\SystemEvent\Http\Api\{
    SystemEventTypeEventsHandle,
    SystemEventViewedHandle,
    SystemEventViewedAllHandle
};
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Infrastructure\Service\AuthorizationCheckerService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

#[OA\Tag(name: 'SystemEvent')]
readonly class SystemEventController
{
    public function __construct(
        private SystemEventTypeEventsHandle $systemEventTypeEventsHandle,
        private AuthorizationCheckerService $authorizationCheckerService,
        private SystemEventViewedHandle $systemEventViewedHandle,
        private SystemEventViewedAllHandle $systemEventViewedAllHandle
    ) {}

    #[OA\Get(
        path: '/api/key/system-events/{type}',
        description: 'Retrieves a paginated list of system events filtered by status (read, unread, all).',
        summary: 'System event list'
    )]
    #[OA\Parameter(
        name: 'type',
        description: 'System event status to filter by',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', enum: ['read', 'unread', 'all'])
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number for pagination',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page for pagination',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'System event list',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'count', type: 'integer', example: 10),
                new OA\Property(property: 'totalCount', type: 'integer', example: 42),
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'result',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 123),
                            new OA\Property(property: 'title', type: 'string', example: 'New comment on your work'),
                            new OA\Property(property: 'owner', type: 'string', example: 'John Doe, PhD'),
                            new OA\Property(property: 'viewed', type: 'boolean', example: false),
                            new OA\Property(property: 'createdAt', type: 'string', example: '2024-10-05 12:34:56')
                        ],
                        type: 'object'
                    )
                )
            ],
            type: 'object'
        )
    )]
    public function list(Request $request, string $type): JsonResponse
    {
        return $this->systemEventTypeEventsHandle->__invoke($request, $type);
    }

    #[OA\Put(
        path: '/api/key/system-events/{id}/viewed',
        description: 'Marks a specific system event as viewed.',
        summary: 'Mark system event as viewed'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'System event recipient ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 123)
    )]
    #[OA\Response(
        response: 204,
        description: 'System event marked as viewed successfully'
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    #[OA\Response(
        response: 404,
        description: 'System event not found'
    )]
    public function viewed(SystemEventRecipient $systemEventRecipient): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::CHANGE_VIEWED->value, $systemEventRecipient);

        return $this->systemEventViewedHandle->__invoke($systemEventRecipient);
    }

    #[OA\Put(
        path: '/api/key/system-events/viewed-all',
        description: 'Marks all system events as viewed.',
        summary: 'Mark all system events as viewed'
    )]
    #[OA\Response(
        response: 204,
        description: 'All system events marked as viewed successfully'
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    public function viewedAll(): JsonResponse
    {
        return $this->systemEventViewedAllHandle->__invoke();
    }
}
