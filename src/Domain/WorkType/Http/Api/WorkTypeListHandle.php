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

namespace App\Domain\WorkType\Http\Api;

use App\Domain\WorkType\DTO\Api\WorkTypeDTO;
use App\Domain\WorkType\Facade\WorkTypeFacade;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class WorkTypeListHandle
{
    public function __construct(
        private WorkTypeFacade $workTypeFacade,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(): JsonResponse
    {
        $types = $this->workTypeFacade->getAll();

        $result = [];
        foreach ($types as $type) {
            $result[] = $this->objectToDtoMapper->map($type, WorkTypeDTO::class);
        }

        return new JsonResponse($result);
    }
}
