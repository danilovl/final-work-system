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

namespace App\Domain\WorkStatus\Http\Api;

use App\Domain\WorkStatus\DTO\Api\WorkStatusDTO;
use App\Domain\WorkStatus\Facade\WorkStatusFacade;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class WorkStatusListHandle
{
    public function __construct(
        private WorkStatusFacade $workStatusFacade,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(): JsonResponse
    {
        $statuses = $this->workStatusFacade->getAll();

        $result = [];
        foreach ($statuses as $status) {
            $result[] = $this->objectToDtoMapper->map($status, WorkStatusDTO::class);
        }

        return new JsonResponse($result);
    }
}
