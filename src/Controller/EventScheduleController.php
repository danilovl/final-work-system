<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Controller;

use App\Constant\VoterSupportConstant;
use App\Entity\EventSchedule;
use App\Model\EventSchedule\Http\{
    EventScheduleListHandle,
    EventScheduleEditHandle,
    EventScheduleCloneHandle,
    EventScheduleCreateHandle,
    EventScheduleDeleteHandle,
    EventScheduleDetailHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class EventScheduleController extends BaseController
{
    public function __construct(
        private EventScheduleCreateHandle $eventScheduleCreateHandle,
        private EventScheduleListHandle $eventScheduleListHandle,
        private EventScheduleDetailHandle $eventScheduleDetailHandle,
        private EventScheduleEditHandle $eventScheduleEditHandle,
        private EventScheduleCloneHandle $eventScheduleCloneHandle,
        private EventScheduleDeleteHandle $eventScheduleDeleteHandle
    ) {
    }

    public function create(Request $request): Response
    {
        return $this->eventScheduleCreateHandle->handle($request);
    }

    public function list(Request $request): Response
    {
        return $this->eventScheduleListHandle->handle($request);
    }

    public function detail(EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $eventSchedule);

        return $this->eventScheduleDetailHandle->handle($eventSchedule);
    }

    public function edit(Request $request, EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventSchedule);

        return $this->eventScheduleEditHandle->handle($request, $eventSchedule);
    }

    public function clone(Request $request, EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CLONE, $eventSchedule);

        return $this->eventScheduleCloneHandle->handle($request, $eventSchedule);
    }

    public function delete(Request $request, EventSchedule $eventSchedule): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventSchedule);

        return $this->eventScheduleDeleteHandle->handle($request, $eventSchedule);
    }
}
