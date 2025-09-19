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

namespace App\Domain\Event\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\Event\Http\{
    EventListHandle,
    EventEditHandle,
    EventDeleteHandle,
    EventDetailHandle,
    EventSwitchToSkypeHandle
};
use App\Domain\Event\Entity\Event;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request,
    Response
};

readonly class EventController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventListHandle $eventListHandle,
        private EventDetailHandle $eventDetailHandle,
        private EventEditHandle $eventEditHandle,
        private EventSwitchToSkypeHandle $eventSwitchToSkypeHandle,
        private EventDeleteHandle $eventDeleteHandle
    ) {}

    public function list(Request $request): Response
    {
        return $this->eventListHandle->__invoke($request);
    }

    public function detail(Request $request, Event $event): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $event);

        return $this->eventDetailHandle->__invoke($request, $event);
    }

    public function edit(Request $request, Event $event): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $event);

        return $this->eventEditHandle->__invoke($request, $event);
    }

    public function switchToSkype(Event $event): RedirectResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::SWITCH_TO_SKYPE->value, $event);

        return $this->eventSwitchToSkypeHandle->__invoke($event);
    }

    public function delete(Request $request, Event $event): RedirectResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $event);

        return $this->eventDeleteHandle->__invoke($request, $event);
    }
}
