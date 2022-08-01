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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventAddressController extends AbstractController
{
    public function __construct(
        private readonly EventAddressCreateHandle $eventAddressCreateHandle,
        private readonly EventAddressEditHandle $eventAddressEditHandle,
        private readonly EventAddressDeleteHandle $eventAddressDeleteHandle
    ) {}

    public function create(Request $request): JsonResponse
    {
        return $this->eventAddressCreateHandle->handle($request);
    }

    public function edit(
        Request $request,
        EventAddress $eventAddress
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventAddress);

        return $this->eventAddressEditHandle->handle($request, $eventAddress);
    }

    public function delete(EventAddress $eventAddress): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventAddress);

        return $this->eventAddressDeleteHandle->handle($eventAddress);
    }
}
