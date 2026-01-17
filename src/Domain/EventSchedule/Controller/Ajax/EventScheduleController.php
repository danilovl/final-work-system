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

namespace App\Domain\EventSchedule\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Http\Ajax\{
    EventScheduleCloneHandle,
    EventScheduleDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class EventScheduleController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventScheduleCloneHandle $eventScheduleCloneHandle,
        private EventScheduleDeleteHandle $eventScheduleDeleteHandle
    ) {}

    public function clone(Request $request, EventSchedule $eventSchedule): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::CLONE->value, $eventSchedule);

        return $this->eventScheduleCloneHandle->__invoke($request, $eventSchedule);
    }

    public function delete(EventSchedule $eventSchedule): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $eventSchedule);

        return $this->eventScheduleDeleteHandle->__invoke($eventSchedule);
    }
}
