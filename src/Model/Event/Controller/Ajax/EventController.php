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

namespace App\Model\Event\Controller\Ajax;

use App\Attribute\AjaxRequestMiddlewareAttribute;
use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Model\Event\Entity\Event;
use App\Model\Event\Http\Ajax\{
    EventEditHandle,
    EventDeleteHandle,
    EventGetEventHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventController extends BaseController
{
    public function __construct(
        private EventGetEventHandle $eventGetEventHandle,
        private EventEditHandle $eventEditHandle,
        private EventDeleteHandle $eventDeleteHandle
    ) {
    }

    #[AjaxRequestMiddlewareAttribute([
        'class' => 'App\Middleware\Event\Ajax\GetEventMiddleware'
    ])]
    public function getEvent(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $event);

        return $this->eventGetEventHandle->handle($request, $event);
    }

    public function edit(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        return $this->eventEditHandle->handle($request, $event);
    }

    public function delete(Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $event);

        return $this->eventDeleteHandle->handle($event);
    }
}
