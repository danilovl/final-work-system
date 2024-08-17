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

use App\Application\Constant\VoterSupportConstant;
use App\Application\Middleware\Event\Ajax\GetEventMiddleware;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\Event\Entity\Event;
use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use App\Domain\Event\Http\Ajax\{
    EventDeleteHandle,
    EventEditHandle,
    EventGetEventHandle
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class EventController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventGetEventHandle $eventGetEventHandle,
        private EventEditHandle $eventEditHandle,
        private EventDeleteHandle $eventDeleteHandle
    ) {}

    #[PermissionMiddleware(service: [
        'name' => GetEventMiddleware::class
    ])]
    public function getEvent(Request $request, Event $event): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $event);

        return $this->eventGetEventHandle->handle($request, $event);
    }

    public function edit(Request $request, Event $event): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $event);

        return $this->eventEditHandle->handle($request, $event);
    }

    public function delete(Event $event): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $event);

        return $this->eventDeleteHandle->handle($event);
    }
}
