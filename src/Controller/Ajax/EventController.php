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

use App\Attribute\AjaxRequestMiddlewareAttribute;
use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventController extends BaseController
{
    #[AjaxRequestMiddlewareAttribute([
        'class' => 'App\Middleware\Event\Ajax\GetEventMiddleware'
    ])]
    public function getEvent(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $event);

        return $this->get('app.http_handle_ajax.event.get_event')->handle($request, $event);
    }

    public function edit(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        return $this->get('app.http_handle_ajax.event.edit')->handle($request, $event);
    }

    public function delete(Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $event);

        return $this->get('app.http_handle_ajax.event.delete')->handle($event);
    }
}
