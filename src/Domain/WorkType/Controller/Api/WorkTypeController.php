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

namespace App\Domain\WorkType\Controller\Api;

use App\Domain\WorkType\DTO\Api\WorkTypeDTO;
use App\Domain\WorkType\Http\Api\WorkTypeListHandle;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

#[OA\Tag(name: 'Work Type')]
readonly class WorkTypeController
{
    public function __construct(
        private WorkTypeListHandle $workTypeListHandle
    ) {}

    #[OA\Get(
        path: '/api/key/work-types',
        description: 'Retrieves all work types.',
        summary: 'Work type list'
    )]
    #[OA\Response(
        response: 200,
        description: 'List of work types',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: WorkTypeDTO::class))
        )
    )]
    public function list(): JsonResponse
    {
        return $this->workTypeListHandle->__invoke();
    }
}
