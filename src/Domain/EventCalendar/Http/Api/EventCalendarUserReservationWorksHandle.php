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

namespace App\Domain\EventCalendar\Http\Api;

use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\DTO\Api\WorkDTO;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\Work\Bus\Query\UserWorks\UserWorksQueryTrait;

readonly class EventCalendarUserReservationWorksHandle
{
    use UserWorksQueryTrait;

    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService,
        private QueryBusInterface $queryBus,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(): JsonResponse
    {
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $userWorks = $this->getUserWorks(
            $this->userService->getUser(),
            WorkUserTypeConstant::AUTHOR->value,
            null,
            $workStatus
        );

        $result = [];
        foreach ($userWorks as $work) {
            $result[] = $this->objectToDtoMapper->map($work, WorkDTO::class);
        }

        return new JsonResponse($result);
    }
}
