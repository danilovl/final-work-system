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

namespace App\Domain\EventAddress\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\EventAddress\Http\{
    EventAddressEditHandle,
    EventAddressDeleteHandle,
    EventAddressDetailHandle,
    EventAddressListHandle,
    EventAddressCreateHandle
};
use App\Domain\EventAddress\Entity\EventAddress;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request,
    Response
};

readonly class EventAddressController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventAddressListHandle $eventAddressListHandle,
        private EventAddressCreateHandle $eventAddressCreateHandle,
        private EventAddressDetailHandle $eventAddressDetailHandle,
        private EventAddressEditHandle $eventAddressEditHandle,
        private EventAddressDeleteHandle $eventAddressDeleteHandle
    ) {}

    public function list(Request $request): Response
    {
        return $this->eventAddressListHandle->__invoke($request);
    }

    public function create(Request $request): Response
    {
        return $this->eventAddressCreateHandle->__invoke($request);
    }

    public function detail(EventAddress $eventAddress): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $eventAddress);

        return $this->eventAddressDetailHandle->__invoke($eventAddress);
    }

    public function edit(Request $request, EventAddress $eventAddress): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $eventAddress);

        return $this->eventAddressEditHandle->__invoke($request, $eventAddress);
    }

    public function delete(Request $request, EventAddress $eventAddress): RedirectResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $eventAddress);

        return $this->eventAddressDeleteHandle->__invoke($request, $eventAddress);
    }
}
