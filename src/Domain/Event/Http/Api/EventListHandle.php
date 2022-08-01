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

namespace App\Domain\Event\Http\Api;

use App\Domain\Work\Service\WorkDetailTabService;
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use App\Application\Constant\TabTypeConstant;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventListHandle
{
    public function __construct(
        private readonly WorkDetailTabService $workDetailTabService,
        private readonly ObjectToArrayTransformService $objectToArrayTransformService
    ) {}

    public function handle(Request $request, Work $work): JsonResponse
    {
        $pagination = $this->workDetailTabService->getTabPagination($request, TabTypeConstant::TAB_EVENT, $work);

        $events = [];
        foreach ($pagination as $event) {
            $events[] = $this->objectToArrayTransformService->transform('api_key_field', $event);
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'success' => true,
            'result' => $events
        ]);
    }
}
