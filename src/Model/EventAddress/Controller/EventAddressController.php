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

namespace App\Model\EventAddress\Controller;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Model\EventAddress\Entity\EventAddress;
use App\Model\EventAddress\Http\{
    EventAddressListHandle,
    EventAddressEditHandle,
    EventAddressCreateHandle,
    EventAddressDeleteHandle,
    EventAddressDetailHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class EventAddressController extends BaseController
{
    public function __construct(
        private EventAddressListHandle $eventAddressListHandle,
        private EventAddressCreateHandle $eventAddressCreateHandle,
        private EventAddressDetailHandle $eventAddressDetailHandle,
        private EventAddressEditHandle $eventAddressEditHandle,
        private EventAddressDeleteHandle $eventAddressDeleteHandle
    ) {
    }

    public function list(Request $request): Response
    {
        return $this->eventAddressListHandle->handle($request);
    }

    public function create(Request $request): Response
    {
        return $this->eventAddressCreateHandle->handle($request);
    }

    public function detail(EventAddress $eventAddress): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $eventAddress);

        return $this->eventAddressDetailHandle->handle($eventAddress);
    }

    public function edit(Request $request, EventAddress $eventAddress): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventAddress);

        return $this->eventAddressEditHandle->handle($request, $eventAddress);
    }

    public function delete(Request $request, EventAddress $eventAddress): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventAddress);

        return $this->eventAddressDeleteHandle->handle($request, $eventAddress);
    }
}
