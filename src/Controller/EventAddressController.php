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

namespace App\Controller;

use App\Constant\VoterSupportConstant;
use App\Entity\EventAddress;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class EventAddressController extends BaseController
{
    public function list(Request $request): Response
    {
        return $this->get('app.http_handle.event_address.list')->handle($request);
    }

    public function create(Request $request): Response
    {
        return $this->get('app.http_handle.event_address.create')->handle($request);
    }

    public function detail(EventAddress $eventAddress): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $eventAddress);

        return $this->get('app.http_handle.event_address.detail')->handle($eventAddress);
    }

    public function edit(Request $request, EventAddress $eventAddress): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $eventAddress);

        return $this->get('app.http_handle.event_address.edit')->handle($request, $eventAddress);
    }

    public function delete(Request $request, EventAddress $eventAddress): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventAddress);

        return $this->get('app.http_handle.event_address.delete')->handle($request, $eventAddress);
    }
}
