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

namespace App\Domain\Event\Controller\Ajax;

use App\Application\Attribute\AjaxRequestMiddlewareAttribute;
use App\Application\Constant\VoterSupportConstant;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Http\Ajax\{
    EventDeleteHandle,
    EventEditHandle,
    EventGetEventHandle
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    public function __construct(
        private EventGetEventHandle $eventGetEventHandle,
        private EventEditHandle $eventEditHandle,
        private EventDeleteHandle $eventDeleteHandle
    ) {
    }

    #[AjaxRequestMiddlewareAttribute([
        'class' => 'App\Application\Middleware\Event\Ajax\GetEventMiddleware'
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