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

namespace App\Domain\EventAddress\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Http\Ajax\{
    EventAddressEditHandle,
    EventAddressCreateHandle,
    EventAddressDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class EventAddressController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventAddressCreateHandle $eventAddressCreateHandle,
        private EventAddressEditHandle $eventAddressEditHandle,
        private EventAddressDeleteHandle $eventAddressDeleteHandle
    ) {}

    public function create(Request $request): JsonResponse
    {
        return $this->eventAddressCreateHandle->__invoke($request);
    }

    public function edit(
        Request $request,
        EventAddress $eventAddress
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $eventAddress);

        return $this->eventAddressEditHandle->__invoke($request, $eventAddress);
    }

    public function delete(EventAddress $eventAddress): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $eventAddress);

        return $this->eventAddressDeleteHandle->__invoke($eventAddress);
    }
}
