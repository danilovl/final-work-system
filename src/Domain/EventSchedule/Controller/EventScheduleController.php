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

namespace App\Domain\EventSchedule\Controller;

use App\Application\Constant\VoterSupportConstant;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventScheduleController extends AbstractController
{
    public function __construct(
        private readonly EventScheduleCreateHandle $eventScheduleCreateHandle,
        private readonly EventScheduleListHandle $eventScheduleListHandle,
        private readonly EventScheduleDetailHandle $eventScheduleDetailHandle,
        private readonly EventScheduleEditHandle $eventScheduleEditHandle,
        private readonly EventScheduleCloneHandle $eventScheduleCloneHandle,
        private readonly EventScheduleDeleteHandle $eventScheduleDeleteHandle
    ) {}

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
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $eventSchedule);

        return $this->eventScheduleDetailHandle->handle($eventSchedule);
    }

    public function edit(Request $request, EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $eventSchedule);

        return $this->eventScheduleEditHandle->handle($request, $eventSchedule);
    }

    public function clone(Request $request, EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CLONE->value, $eventSchedule);

        return $this->eventScheduleCloneHandle->handle($request, $eventSchedule);
    }

    public function delete(Request $request, EventSchedule $eventSchedule): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $eventSchedule);

        return $this->eventScheduleDeleteHandle->handle($request, $eventSchedule);
    }
}
