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

namespace App\Domain\EventSchedule\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\EventSchedule\Http\{
    EventScheduleEditHandle,
    EventScheduleListHandle,
    EventScheduleCloneHandle,
    EventScheduleCreateHandle,
    EventScheduleDeleteHandle,
    EventScheduleDetailHandle
};
use App\Domain\EventSchedule\Entity\EventSchedule;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request,
    Response
};

readonly class EventScheduleController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventScheduleCreateHandle $eventScheduleCreateHandle,
        private EventScheduleListHandle $eventScheduleListHandle,
        private EventScheduleDetailHandle $eventScheduleDetailHandle,
        private EventScheduleEditHandle $eventScheduleEditHandle,
        private EventScheduleCloneHandle $eventScheduleCloneHandle,
        private EventScheduleDeleteHandle $eventScheduleDeleteHandle
    ) {}

    public function create(Request $request): Response
    {
        return $this->eventScheduleCreateHandle->__invoke($request);
    }

    public function list(Request $request): Response
    {
        return $this->eventScheduleListHandle->__invoke($request);
    }

    public function detail(EventSchedule $eventSchedule): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $eventSchedule);

        return $this->eventScheduleDetailHandle->__invoke($eventSchedule);
    }

    public function edit(Request $request, EventSchedule $eventSchedule): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $eventSchedule);

        return $this->eventScheduleEditHandle->__invoke($request, $eventSchedule);
    }

    public function clone(Request $request, EventSchedule $eventSchedule): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::CLONE->value, $eventSchedule);

        return $this->eventScheduleCloneHandle->__invoke($request, $eventSchedule);
    }

    public function delete(Request $request, EventSchedule $eventSchedule): RedirectResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $eventSchedule);

        return $this->eventScheduleDeleteHandle->__invoke($request, $eventSchedule);
    }
}
