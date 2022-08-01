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

namespace App\Domain\EventSchedule\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Http\Ajax\{
    EventScheduleCloneHandle,
    EventScheduleDeleteHandle
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventScheduleController extends AbstractController
{
    public function __construct(
        private readonly EventScheduleCloneHandle $eventScheduleCloneHandle,
        private readonly EventScheduleDeleteHandle $eventScheduleDeleteHandle
    ) {}

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