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

namespace App\Domain\WorkStatus\Controller\Api;

use App\Domain\WorkStatus\DTO\Api\WorkStatusDTO;
use App\Domain\WorkStatus\Http\Api\WorkStatusListHandle;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

#[OA\Tag(name: 'Work Status')]
readonly class WorkStatusController
{
    public function __construct(
        private WorkStatusListHandle $workStatusListHandle
    ) {}

    #[OA\Get(
        path: '/api/key/work-statuses',
        description: 'Retrieves all work statuses.',
        summary: 'Work status list'
    )]
    #[OA\Response(
        response: 200,
        description: 'List of work statuses',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: WorkStatusDTO::class))
        )
    )]
    public function list(): JsonResponse
    {
        return $this->workStatusListHandle->__invoke();
    }
}
