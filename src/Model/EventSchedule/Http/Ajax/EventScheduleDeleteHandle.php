<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\EventSchedule\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Model\EventSchedule\Entity\EventSchedule;
use App\Service\{
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class EventScheduleDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService
    ) {
    }

    public function handle(EventSchedule $eventSchedule): JsonResponse
    {
        $this->entityManagerService->remove($eventSchedule);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
