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
use App\Entity\Event;
use App\Model\Event\Http\{
    EventEditHandle,
    EventDeleteHandle,
    EventDetailHandle,
    EventSwitchToSkypeHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class EventController extends BaseController
{
    public function __construct(
        private EventDetailHandle $eventDetailHandle,
        private EventEditHandle $eventEditHandle,
        private EventSwitchToSkypeHandle $eventSwitchToSkypeHandle,
        private EventDeleteHandle $eventDeleteHandle,
    ) {
    }

    public function detail(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $event);

        return $this->eventDetailHandle->handle($request, $event);
    }

    public function edit(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        return $this->eventEditHandle->handle($request, $event);
    }

    public function switchToSkype(Event $event): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::SWITCH_TO_SKYPE, $event);

        return $this->eventSwitchToSkypeHandle->handle($event);
    }

    public function delete(Request $request, Event $event): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $event);

        return $this->eventDeleteHandle->handle($request, $event);
    }
}
