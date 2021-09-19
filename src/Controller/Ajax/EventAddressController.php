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

namespace App\Controller\Ajax;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Entity\EventAddress;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventAddressController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_ajax.event_address.create')->handle($request);
    }

    public function edit(
        Request $request,
        EventAddress $eventAddress
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventAddress);

        return $this->get('app.http_handle_ajax.event_address.edit')->handle($request, $eventAddress);
    }

    public function delete(EventAddress $eventAddress): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventAddress);

        return $this->get('app.http_handle_ajax.event_address.delete')->handle($eventAddress);
    }
}
