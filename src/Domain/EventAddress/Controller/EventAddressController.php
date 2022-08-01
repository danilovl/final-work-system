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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventAddressController extends AbstractController
{
    public function __construct(
        private readonly EventAddressListHandle $eventAddressListHandle,
        private readonly EventAddressCreateHandle $eventAddressCreateHandle,
        private readonly EventAddressDetailHandle $eventAddressDetailHandle,
        private readonly EventAddressEditHandle $eventAddressEditHandle,
        private readonly EventAddressDeleteHandle $eventAddressDeleteHandle
    ) {}

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
