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
use App\Entity\EventSchedule;
use App\Model\EventSchedule\Http\Ajax\{
    EventScheduleCloneHandle,
    EventScheduleDeleteHandle
};
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class EventScheduleController extends BaseController
{
    public function __construct(
        private EventScheduleCloneHandle $eventScheduleCloneHandle,
        private EventScheduleDeleteHandle $eventScheduleDeleteHandle
    ) {
    }

    public function clone(Request $request, EventSchedule $eventSchedule): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CLONE, $eventSchedule);

        return $this->eventScheduleCloneHandle->handle($request, $eventSchedule);
    }

    public function delete(EventSchedule $eventSchedule): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $eventSchedule);

        return $this->eventScheduleDeleteHandle->handle($eventSchedule);
    }
}