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
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class EventScheduleController extends BaseController
{
    public function create(Request $request): Response
    {
        return $this->get('app.http_handle.event_schedule.create')->handle($request);
    }

    public function list(Request $request): Response
    {
        return $this->get('app.http_handle.event_schedule.list')->handle($request);
    }

    public function detail(EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $eventSchedule);

        return $this->get('app.http_handle.event_schedule.detail')->handle($eventSchedule);
    }

    public function edit(Request $request, EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventSchedule);

        return $this->get('app.http_handle.event_schedule.edit')->handle($request, $eventSchedule);
    }

    public function clone(Request $request, EventSchedule $eventSchedule): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CLONE, $eventSchedule);

        return $this->get('app.http_handle.event_schedule.clone')->handle($request, $eventSchedule);
    }

    public function delete(Request $request, EventSchedule $eventSchedule): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventSchedule);

        return $this->get('app.http_handle.event_schedule.delete')->handle($request, $eventSchedule);
    }
}
