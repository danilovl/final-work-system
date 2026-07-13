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

namespace App\Domain\Event\Controller\Api;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Application\Constant\VoterSupportConstant;
use App\Domain\Event\DTO\Api\EventDTO;
use App\Domain\Event\DTO\Api\Input\EventCreateInput;
use App\Domain\Event\DTO\Api\Output\EventListOwnerOutput;
use App\Domain\Event\Entity\Event;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\Event\Http\Api\{
    EventListHandle,
    EventListOwnerHandle,
    EventCreateHandle,
    EventDeleteHandle,
    EventDetailHandler
};
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class EventController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventListHandle $eventListHandle,
        private EventListOwnerHandle $eventListOwnerHandle,
        private EventCreateHandle $eventCreateHandle,
        private EventDeleteHandle $eventDeleteHandle,
        private EventDetailHandler $eventDetailApiHandle,
        private ValidatorInterface $validator
    ) {}

    public function detail(Event $event): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $event);

        return $this->eventDetailApiHandle->__invoke($event);
    }

    public function list(Request $request, Work $work): JsonResponse
    {
        return $this->eventListHandle->__invoke($request, $work);
    }

    public function listOwner(Request $request): EventListOwnerOutput
    {
        return $this->eventListOwnerHandle->__invoke($request);
    }

    public function create(EventCreateInput $input): EventDTO
    {
        $violations = $this->validator->validate($input);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }

        return $this->eventCreateHandle->__invoke($input);
    }

    public function delete(Event $event): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $event);

        return $this->eventDeleteHandle->__invoke($event);
    }
}
