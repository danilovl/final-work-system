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

namespace App\Domain\WorkCategory\Controller\Api;

use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\WorkCategory\DTO\Api\WorkCategoryDTO;
use App\Domain\WorkCategory\Http\Api\WorkCategoryListHandle;
use App\Infrastructure\Service\AuthorizationCheckerService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

#[OA\Tag(name: 'Work Category')]
readonly class WorkCategoryController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private WorkCategoryListHandle $workCategoryListHandle
    ) {}

    #[OA\Get(
        path: '/api/key/work-categories',
        description: 'Retrieves all work categories for the authenticated supervisor.',
        summary: 'Work category list'
    )]
    #[OA\Response(
        response: 200,
        description: 'List of work categories',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: WorkCategoryDTO::class))
        )
    )]
    public function list(): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(UserRoleConstant::SUPERVISOR->value);

        return $this->workCategoryListHandle->__invoke();
    }
}
